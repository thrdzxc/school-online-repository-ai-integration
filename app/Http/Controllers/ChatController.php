<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\User;
use App\Support\Audit;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isStudent()) {
            $conversations = ChatConversation::with(['room.subject', 'adviser', 'latestMessage'])
                ->where('student_id', $user->id)
                ->whereHas('room.enrollments', fn ($q) => $q->where('student_id', $user->id))
                ->latest('updated_at')
                ->get();

            $rooms = Room::with(['subject.adviser'])
                ->whereHas('enrollments', fn ($q) => $q->where('student_id', $user->id))
                ->get();
        } elseif ($user->isAdviser()) {
            $conversations = ChatConversation::with(['room.subject', 'student', 'latestMessage'])
                ->where('adviser_id', $user->id)
                ->whereHas('room.subject', fn ($q) => $q->where('adviser_id', $user->id))
                ->whereHas('room.enrollments', fn ($q) => $q->whereColumn('enrollments.student_id', 'chat_conversations.student_id'))
                ->latest('updated_at')
                ->get();
            $rooms = collect();
        } else {
            abort(403);
        }

        $unread = ChatMessage::whereHas('conversation', function ($q) use ($user) {
                if ($user->isStudent()) $q->where('student_id', $user->id);
                else $q->where('adviser_id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->get()
            ->groupBy('conversation_id');

        return view('chat.index', compact('conversations', 'rooms', 'unread'));
    }

    public function openRoom(Room $room)
    {
        $user = auth()->user();
        abort_unless($user->isStudent(), 403);

        $enrolled = Enrollment::where('student_id', $user->id)
            ->where('room_id', $room->id)
            ->exists();
        abort_unless($enrolled, 403);

        $room->loadMissing('subject');
        abort_unless($room->subject?->adviser_id, 422, 'This room has no adviser assigned.');

        $conversation = ChatConversation::firstOrCreate([
            'room_id' => $room->id,
            'student_id' => $user->id,
            'adviser_id' => $room->subject->adviser_id,
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function openStudent(Room $room, User $student)
    {
        $user = auth()->user();
        abort_unless($user->isAdviser(), 403);
        $room->loadMissing('subject');
        abort_unless($room->subject && $room->subject->adviser_id === $user->id, 403);
        abort_unless($student->role === 'student', 404);
        abort_unless(Enrollment::where('room_id', $room->id)->where('student_id', $student->id)->exists(), 403);

        $conversation = ChatConversation::firstOrCreate([
            'room_id' => $room->id,
            'student_id' => $student->id,
            'adviser_id' => $user->id,
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function show(ChatConversation $conversation)
    {
        $this->assertParticipant($conversation);
        $conversation->load(['room.subject', 'student', 'adviser', 'messages' => fn ($query) => $query->with('sender')->orderBy('id')]);
        $this->markIncomingRead($conversation, $conversation->messages->last()?->id ?? 0);
        return view('chat.show', compact('conversation'));
    }

    public function send(Request $request, ChatConversation $conversation)
    {
        $this->assertParticipant($conversation);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $body = trim($data['body']);
        if ($body === '') {
            throw ValidationException::withMessages(['body' => 'Type a message before sending.']);
        }

        $message = DB::transaction(function () use ($conversation, $body) {
            $message = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'body' => $body,
            ]);
            $conversation->touch();
            $conversation->loadMissing(['student', 'adviser', 'room']);

            $recipient = auth()->id() === $conversation->student_id ? $conversation->adviser : $conversation->student;
            Notifier::send(
                $recipient->id,
                'New chat message',
                auth()->user()->name.' sent you a message in '.$conversation->room->name.'.',
                route('chat.show', $conversation)
            );

            Audit::log(auth()->user(), 'chat_message_sent', 'chat_message', $message->id, [
                'conversation_id' => $conversation->id,
                'recipient_id' => $recipient->id,
            ]);

            return $message;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->messageData($message->load('sender'))], 201);
        }

        return redirect()->route('chat.show', $conversation)->with('success', 'Message sent.');
    }

    public function messages(Request $request, ChatConversation $conversation)
    {
        $this->assertParticipant($conversation);
        $data = $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $messages = $conversation->messages()->with('sender')
            ->where('id', '>', $data['after'] ?? 0)->orderBy('id')->limit(100)->get();
        $this->markIncomingRead($conversation, $messages->last()?->id ?? ($data['after'] ?? 0));

        return response()->json([
            'messages' => $messages->map(fn ($message) => $this->messageData($message)),
            'has_more' => $messages->count() === 100,
        ])->header('Cache-Control', 'no-store');
    }

    private function messageData(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'body' => $message->body,
            'mine' => $message->sender_id === auth()->id(),
            'time' => $message->created_at->format('M d, h:i A'),
        ];
    }

    private function markIncomingRead(ChatConversation $conversation, int $throughId): void
    {
        $conversation->messages()
            ->where('id', '<=', $throughId)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function assertParticipant(ChatConversation $conversation): void
    {
        $user = auth()->user();
        $conversation->loadMissing('room.subject');

        if ($user->isStudent()) {
            $allowed = $conversation->student_id === $user->id
                && Enrollment::where('room_id', $conversation->room_id)
                    ->where('student_id', $user->id)
                    ->exists();
            abort_unless($allowed, 403);
            return;
        }

        if ($user->isAdviser()) {
            $allowed = $conversation->adviser_id === $user->id
                && $conversation->room?->subject?->adviser_id === $user->id
                && Enrollment::where('room_id', $conversation->room_id)
                    ->where('student_id', $conversation->student_id)->exists();
            abort_unless($allowed, 403);
            return;
        }

        abort(403);
    }
}

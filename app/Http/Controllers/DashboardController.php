<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AiEvaluation;
use App\Models\AppNotification;
use App\Models\ChatMessage;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Room;
use App\Models\Submission;
use App\Models\Subject;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return match ($user->role) {
            'student' => redirect()->route('student.dashboard'),
            'adviser' => redirect()->route('adviser.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => abort(403),
        };
    }

    public function student()
    {
        $user = auth()->user();
        $roomIds = Enrollment::where('student_id', $user->id)->whereNotNull('room_id')->pluck('room_id')->unique();
        $activities = Activity::with(['subject','room'])->forStudent($user->id)
            ->where('status', 'published')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')->take(8)->get();

        $latestSubmissions = Submission::with(['activity.subject','activity.room','grade','latestAiEvaluation'])
            ->where('student_id',$user->id)->latest('submitted_at')->take(6)->get();
        $notifications = AppNotification::where('user_id',$user->id)->latest()->take(5)->get();
        $roomCount = $roomIds->count();
        $unreadChat = ChatMessage::whereHas('conversation', fn ($q) => $q->where('student_id', $user->id))
            ->where('sender_id','!=',$user->id)->whereNull('read_at')->count();

        return view('student.dashboard', compact('activities','latestSubmissions','notifications','roomCount','unreadChat'));
    }

    public function adviser()
    {
        $user = auth()->user();
        $subjects = Subject::withCount(['students','activities'])->where('adviser_id',$user->id)->get();
        $activityIds = Activity::where('adviser_id',$user->id)->pluck('id');
        $pending = Submission::whereIn('activity_id',$activityIds)->whereIn('status',['submitted','ai_failed','ai_reviewed'])->count();
        $recent = Submission::with(['student','activity.subject','activity.room','latestAiEvaluation'])
            ->whereIn('activity_id',$activityIds)->latest('submitted_at')->take(8)->get();
        $roomCount = Room::whereHas('subject', fn ($q) => $q->where('adviser_id',$user->id))->count();
        $unreadChat = ChatMessage::whereHas('conversation', fn ($q) => $q->where('adviser_id', $user->id))
            ->where('sender_id','!=',$user->id)->whereNull('read_at')->count();

        return view('adviser.dashboard', compact('subjects','pending','recent','roomCount','unreadChat'));
    }

    public function admin()
    {
        $counts = [
            'students' => User::where('role','student')->count(),
            'advisers' => User::where('role','adviser')->count(),
            'subjects' => Subject::count(),
            'rooms' => Room::count(),
            'activities' => Activity::count(),
            'submissions' => Submission::count(),
            'ai' => AiEvaluation::count(),
            'grades' => Grade::count(),
        ];
        $recentAi = AiEvaluation::with('submission.student','submission.activity')->latest('evaluated_at')->take(8)->get();
        return view('admin.dashboard', compact('counts','recentAi'));
    }
}

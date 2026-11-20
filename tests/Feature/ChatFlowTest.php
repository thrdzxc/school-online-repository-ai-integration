<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $adviser;
    private ChatConversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $this->student = User::where('email', 'student@school.test')->firstOrFail();
        $room = $this->student->rooms()->with('subject.adviser')->firstOrFail();
        $this->adviser = $room->subject->adviser;
        $this->conversation = ChatConversation::create([
            'room_id' => $room->id,
            'student_id' => $this->student->id,
            'adviser_id' => $this->adviser->id,
        ]);
    }

    public function test_participants_can_exchange_messages_without_reloading_and_receive_notifications(): void
    {
        $url = route('chat.send', $this->conversation);
        $sent = $this->actingAs($this->student)->postJson($url, ['body' => " Hello adviser\nSecond line "])
            ->assertCreated()->assertJsonPath('message.body', "Hello adviser\nSecond line")
            ->assertJsonPath('message.mine', true)->json('message.id');

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->adviser->id, 'title' => 'New chat message',
            'url' => route('chat.show', $this->conversation),
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'chat_message_sent', 'target_id' => $sent]);
        $this->actingAs($this->adviser)->getJson($url.'?after=0')
            ->assertOk()->assertJsonPath('messages.0.mine', false)
            ->assertJsonPath('messages.0.id', $sent);
        $this->assertNotNull(ChatMessage::findOrFail($sent)->read_at);

        $reply = $this->actingAs($this->adviser)->postJson($url, ['body' => 'Hello student'])
            ->assertCreated()->json('message.id');
        $this->actingAs($this->student)->getJson($url.'?after='.$sent)
            ->assertOk()->assertJsonCount(1, 'messages')->assertJsonPath('messages.0.id', $reply);
    }

    public function test_polling_returns_all_messages_in_order_across_batches_without_losing_history(): void
    {
        $ids = [];
        for ($i = 0; $i < 105; $i++) {
            $ids[] = ChatMessage::create([
                'conversation_id' => $this->conversation->id,
                'sender_id' => $this->student->id, 'body' => 'Message '.$i,
            ])->id;
        }
        $url = route('chat.messages', $this->conversation);
        $first = $this->actingAs($this->adviser)->getJson($url.'?after=0')
            ->assertOk()->assertJsonCount(100, 'messages')->assertJsonPath('has_more', true)
            ->json('messages');
        $this->assertSame(array_slice($ids, 0, 100), array_column($first, 'id'));
        $this->assertNull(ChatMessage::findOrFail($ids[104])->read_at);
        $second = $this->getJson($url.'?after='.$ids[99])
            ->assertOk()->assertJsonCount(5, 'messages')->assertJsonPath('has_more', false)
            ->json('messages');
        $this->assertSame(array_slice($ids, 100), array_column($second, 'id'));
        $this->getJson($url.'?after='.$ids[104])->assertOk()->assertJsonCount(0, 'messages');
        $this->get(route('chat.show', $this->conversation))->assertOk()
            ->assertSee('Message 0')->assertSee('Message 104');
    }

    public function test_invalid_messages_and_cursors_are_rejected_without_writing_messages(): void
    {
        $url = route('chat.send', $this->conversation);
        foreach (['', '   ', str_repeat('x', 3001)] as $body) {
            $this->actingAs($this->student)->postJson($url, compact('body'))
                ->assertUnprocessable()->assertJsonValidationErrors('body');
        }
        $this->getJson($url.'?after=-1')->assertUnprocessable()->assertJsonValidationErrors('after');
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_unrelated_users_and_former_students_cannot_read_or_send_private_messages(): void
    {
        $url = route('chat.send', $this->conversation);
        foreach (['student', 'adviser', 'admin'] as $role) {
            $other = User::factory()->create(['role' => $role, 'active' => true]);
            $this->actingAs($other)->getJson($url)->assertForbidden();
            $this->postJson($url, ['body' => 'Unauthorized'])->assertForbidden();
            $this->get(route('chat.show', $this->conversation))->assertForbidden();
        }
        $this->student->enrollments()->where('room_id', $this->conversation->room_id)->delete();
        $this->actingAs($this->student)->getJson($url)->assertForbidden();
        $this->postJson($url, ['body' => 'No longer enrolled'])->assertForbidden();
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_form_fallback_redirects_and_message_markup_is_escaped(): void
    {
        $this->actingAs($this->student)->post(route('chat.send', $this->conversation), [
            'body' => '<script>alert("test")</script>',
        ])->assertRedirect(route('chat.show', $this->conversation));
        $this->get(route('chat.show', $this->conversation))->assertOk()
            ->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert', false);
    }

    public function test_notification_failure_does_not_leave_a_partially_sent_message(): void
    {
        \App\Models\AppNotification::creating(function () {
            throw new \RuntimeException('Simulated notification storage failure');
        });
        $this->actingAs($this->student)->postJson(route('chat.send', $this->conversation), [
            'body' => 'This message must roll back.',
        ])->assertStatus(500);
        $this->assertDatabaseCount('chat_messages', 0);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'chat_message_sent']);
    }
}

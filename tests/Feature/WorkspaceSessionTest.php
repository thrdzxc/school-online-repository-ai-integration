<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceSessionTest extends TestCase
{
    use RefreshDatabase;

    private array $cookies = [];
    private string $sessionDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionDirectory = storage_path('framework/testing-workspaces-'.bin2hex(random_bytes(8)));
        mkdir($this->sessionDirectory, 0775, true);
        config(['session.driver' => 'file', 'session.files' => $this->sessionDirectory]);
        foreach (['student', 'adviser', 'admin'] as $role) {
            User::factory()->create(['role' => $role, 'email' => $role.'@school.test', 'password' => bcrypt('password'), 'active' => true]);
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->sessionDirectory.'/*') as $file) unlink($file);
        rmdir($this->sessionDirectory);
        parent::tearDown();
    }

    private function visit(string $role, string $method, string $path, array $data = [])
    {
        // One browser cookie jar, as used by multiple Chrome tabs.
        foreach (app('cookie')->getQueuedCookies() as $cookie) app('cookie')->unqueue($cookie->getName(), $cookie->getPath());
        $this->unencryptedCookies = $this->cookies;
        $response = $this->call($method, '/workspaces/'.$role.$path, $data, $this->cookies, [], ['HTTP_REFERER' => rtrim(config('app.url'), '/').'/workspaces/'.$role.'/login']);
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getExpiresTime() && $cookie->getExpiresTime() < time()) unset($this->cookies[$cookie->getName()]);
            else $this->cookies[$cookie->getName()] = $cookie->getValue();
        }
        return $response;
    }

    private function signIn(string $role): void
    {
        $this->visit($role, 'GET', '/login')->assertOk()->assertSee('/workspaces/'.$role.'/login', false);
        $this->visit($role, 'POST', '/login', ['email' => $role.'@school.test', 'password' => 'password', 'remember' => 1])
            ->assertRedirect('/workspaces/'.$role.'/'.$role.'/dashboard');
    }

    public function test_switching_adviser_accounts_starts_with_the_new_accounts_own_workspace(): void
    {
        $this->signIn('adviser');
        $existing = User::where('email', 'adviser@school.test')->firstOrFail();
        \App\Models\Subject::create(['name' => 'Existing Adviser Private Subject', 'adviser_id' => $existing->id, 'school_year' => '2026-2027', 'semester' => 'First Semester', 'active' => true]);
        $this->visit('adviser', 'GET', '/adviser/rooms')->assertOk()->assertSee('Existing Adviser Private Subject');
        $this->visit('adviser', 'POST', '/logout')->assertRedirect('/workspaces/adviser/login');
        User::factory()->create(['name' => 'New Adviser', 'role' => 'adviser', 'email' => 'new.adviser@example.com', 'password' => bcrypt('NewPassword!'), 'active' => true]);
        $this->visit('adviser', 'POST', '/login', ['email' => 'new.adviser@example.com', 'password' => 'NewPassword!'])
            ->assertRedirect('/workspaces/adviser/adviser/dashboard');
        $this->visit('adviser', 'GET', '/adviser/dashboard')->assertOk()->assertSee('Welcome, New Adviser')->assertSee('Set Up My First Subject');
        $this->visit('adviser', 'GET', '/adviser/rooms')->assertOk()->assertDontSee('Existing Adviser Private Subject');
    }

    public function test_three_roles_remain_signed_in_and_logging_out_one_does_not_affect_the_others(): void
    {
        foreach (['student', 'adviser', 'admin'] as $role) $this->signIn($role);
        foreach (['student', 'adviser', 'admin'] as $role) {
            $this->visit($role, 'GET', '/profile')->assertOk()->assertSee($role.'@school.test')
                ->assertSee('/workspaces/'.$role.'/profile', false)
                ->assertSee('/workspaces/'.$role.'/logout', false);
        }
        $this->assertCount(3, array_filter(array_keys($this->cookies), fn ($key) => str_starts_with($key, 'remember_web_')));
        $this->visit('admin', 'POST', '/logout')->assertRedirect('/workspaces/admin/login');
        $this->visit('admin', 'GET', '/profile')->assertRedirect('/workspaces/admin/login');
        $this->visit('student', 'GET', '/profile')->assertOk()->assertSee('student@school.test');
        $this->visit('adviser', 'GET', '/profile')->assertOk()->assertSee('adviser@school.test');
    }

    public function test_workspace_does_not_bypass_role_checks_and_notifications_stay_in_the_readers_workspace(): void
    {
        $this->visit('student', 'POST', '/login', ['email' => 'admin@school.test', 'password' => 'password', 'portal' => 'admin'])
            ->assertRedirect('/workspaces/student/login');
        $this->visit('student', 'GET', '/login')->assertSee('This account belongs to the Admin portal.');
        $this->visit('student', 'GET', '/profile')->assertRedirect('/workspaces/student/login');
        $this->signIn('student');
        $this->visit('student', 'GET', '/admin/dashboard')->assertForbidden();
        $student = User::where('role', 'student')->first();
        $notification = AppNotification::create(['user_id' => $student->id, 'title' => 'Update', 'message' => 'New grade', 'url' => url('/workspaces/adviser/student/grades')]);
        $this->visit('student', 'POST', '/notifications/'.$notification->id.'/read')->assertRedirect('/workspaces/student/student/grades');
        $this->assertNotNull($notification->fresh()->read_at);
        $this->get('/workspaces/unknown/login')->assertNotFound();
    }

    public function test_chat_links_model_binding_and_notifications_work_between_two_signed_in_tabs(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $student = User::where('email', 'student@school.test')->firstOrFail();
        $room = $student->rooms()->firstOrFail();
        $this->signIn('student');
        $this->signIn('adviser');
        $this->visit('student', 'GET', '/chat/rooms/'.$room->id)->assertRedirect();
        $conversation = \App\Models\ChatConversation::firstOrFail();
        $this->visit('student', 'GET', '/chat/'.$conversation->id)->assertOk()
            ->assertSee('/workspaces/student/chat/'.$conversation->id.'/messages', false);
        $this->visit('student', 'POST', '/chat/'.$conversation->id.'/messages', ['body' => 'Message from the student tab'])->assertRedirect();
        $notification = AppNotification::where('user_id', $conversation->adviser_id)->where('title', 'New chat message')->firstOrFail();
        $this->assertStringNotContainsString('/workspaces/', $notification->url);
        $this->visit('adviser', 'POST', '/notifications/'.$notification->id.'/read')
            ->assertRedirect('/workspaces/adviser/chat/'.$conversation->id);
        $this->visit('adviser', 'GET', '/chat/'.$conversation->id)->assertOk()->assertSee('Message from the student tab');
        $this->visit('adviser', 'POST', '/chat/'.$conversation->id.'/messages', ['body' => 'Reply from the adviser tab'])->assertRedirect();
        $this->visit('student', 'GET', '/chat/'.$conversation->id.'/messages')->assertOk()->assertJsonPath('messages.1.body', 'Reply from the adviser tab');
    }

    public function test_sessions_and_csrf_tokens_are_independent_without_remember_me(): void
    {
        $tokens = [];
        foreach (['student', 'adviser', 'admin'] as $role) {
            $this->visit($role, 'POST', '/login', ['email' => $role.'@school.test', 'password' => 'password'])->assertRedirect();
        }
        foreach (['student', 'adviser', 'admin'] as $role) {
            $response = $this->visit($role, 'GET', '/profile')->assertOk()->assertSee($role.'@school.test');
            preg_match('/name="csrf-token" content="([^"]+)"/', $response->getContent(), $match);
            $tokens[] = $match[1];
            foreach ($response->headers->getCookies() as $cookie) {
                if (str_ends_with($cookie->getName(), '_'.$role)) {
                    $this->assertSame('/workspaces/'.$role, $cookie->getPath());
                    $this->assertTrue($cookie->isHttpOnly());
                }
            }
        }
        $this->assertCount(3, array_unique($tokens));
        $this->assertCount(0, array_filter(array_keys($this->cookies), fn ($key) => str_starts_with($key, 'remember_web_')));
    }

    public function test_remembered_logins_restore_only_the_selected_workspace(): void
    {
        foreach (['student', 'adviser', 'admin'] as $role) $this->signIn($role);
        // Simulate loss of all short-lived sessions while retaining remember cookies.
        $this->cookies = array_filter($this->cookies, fn ($key) => str_starts_with($key, 'remember_web_'), ARRAY_FILTER_USE_KEY);
        foreach (['student', 'adviser', 'admin'] as $role) {
            $this->visit($role, 'GET', '/profile')->assertOk()->assertSee($role.'@school.test');
        }
    }
}

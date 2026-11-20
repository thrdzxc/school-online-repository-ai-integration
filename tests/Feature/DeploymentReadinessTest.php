<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_data_endpoints_require_an_active_admin_token(): void
    {
        $this->getJson('/api/employees')->assertUnauthorized();
        $this->postJson('/api/students', ['name' => 'New Student', 'course' => 'BSIT'])->assertUnauthorized();

        foreach (['student', 'adviser', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role, 'active' => true]);
            $token = $user->createToken('deployment-test')->plainTextToken;
            $response = $this->withToken($token)->getJson('/api/employees');
            $response->assertStatus($role === 'admin' ? 200 : 403);
            $this->app['auth']->forgetGuards();
            $response = $this->withToken($token)->postJson('/api/students', ['name' => 'New Student', 'course' => 'BSIT']);
            $response->assertStatus($role === 'admin' ? 201 : 403);
            $this->app['auth']->forgetGuards();

            $user->update(['active' => false]);
            $this->withToken($token)->getJson('/api/employees')->assertForbidden();
            $this->app['auth']->forgetGuards();
        }
        $this->assertDatabaseCount('students', 1);
    }

    public function test_disabled_users_cannot_obtain_api_tokens(): void
    {
        $user = User::factory()->create(['active' => false, 'password' => 'ValidPassword123!']);
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'ValidPassword123!'])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_registration_login_and_token_logout(): void
    {
        $credentials = ['email' => 'new.student@example.com', 'password' => 'ValidPassword123!'];
        $response = $this->postJson('/api/register', $credentials + ['name' => 'New Student', 'role' => 'admin'])
            ->assertCreated()->assertJsonStructure(['token', 'user']);
        $this->assertDatabaseHas('users', ['email' => $credentials['email'], 'role' => 'student']);
        $token = $response->json('token');
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->postJson('/api/logout')->assertUnauthorized();
        $this->postJson('/api/login', $credentials)->assertOk()->assertJsonStructure(['token']);
    }

    public function test_admin_employee_api_create_search_update_and_delete(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $department = \Illuminate\Support\Facades\DB::table('departments')->insertGetId(['name' => 'IT']);
        $this->withToken($admin->createToken('deployment-test')->plainTextToken);
        $employee = $this->postJson('/api/employees', [
            'first_name' => 'Test', 'last_name' => 'Employee', 'email' => 'employee@example.com',
            'department' => 'IT', 'department_id' => $department, 'position' => 'Instructor',
        ])->assertCreated()->json('id');
        $this->getJson('/api/employees?search=employee@example.com')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/employees/'.$employee)->assertOk();
        $this->putJson('/api/employees/'.$employee, ['position' => 'Senior Instructor'])->assertOk();
        $this->assertDatabaseHas('employees', ['id' => $employee, 'position' => 'Senior Instructor']);
        $this->deleteJson('/api/employees/'.$employee)->assertOk();
        $this->getJson('/api/employees/'.$employee)->assertNotFound();
    }

    public function test_api_login_validates_password_type_and_throttles_attempts(): void
    {
        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => ['invalid']])->assertUnprocessable();
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'InvalidPassword123!'])->assertUnauthorized();
        }
        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'InvalidPassword123!'])->assertStatus(429);
    }

    public function test_csv_import_accepts_multiple_accounts_without_school_ids(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $csv = "name,email,password,role,school_id\nFirst Student,first@example.com,ValidPassword123!,student,\nSecond Student,second@example.com,ValidPassword123!,student,\n";
        $this->actingAs($admin)->post(route('admin.users.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('users.csv', $csv),
        ])->assertSessionHasNoErrors();
        foreach (['first@example.com', 'second@example.com'] as $email) {
            $this->assertDatabaseHas('users', ['email' => $email, 'school_id' => null]);
        }
    }

    public function test_invalid_csv_row_rolls_back_the_whole_import(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $csv = "name,email,password,role\nFirst Student,first@example.com,ValidPassword123!,student\nSecond Student,invalid,ValidPassword123!,student\n";
        $this->actingAs($admin)->post(route('admin.users.import'), [
            'csv_file' => UploadedFile::fake()->createWithContent('users.csv', $csv),
        ])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('users', ['email' => 'first@example.com']);
    }
}

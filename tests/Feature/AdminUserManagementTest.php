<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_view_user_management(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        User::factory()->unverified()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('Admin.admin')
            ->assertViewHas('users', fn ($users) => $users->count() === 3)
            ->assertSee('User Management (Admin CRUD Controls)')
            ->assertSee('Registered')
            ->assertSee('Not registered');
    }

    public function test_admin_can_create_update_and_delete_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.users.store'), [
            'name' => 'New Student',
            'email' => 'new-student@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'role' => 'student',
        ])->assertRedirect(route('admin.dashboard'));

        $user = User::where('email', 'new-student@example.com')->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertTrue(Hash::check('password-123', $user->password));
        $this->assertNull($user->email_verified_at);

        $this->put(route('admin.users.update', $user), [
            'name' => 'Updated Student',
            'email' => 'updated-student@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'admin',
        ])->assertRedirect(route('admin.dashboard'));

        $user->refresh();
        $this->assertSame('Updated Student', $user->name);
        $this->assertSame('admin', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password-123', $user->password));

        $this->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_administrators_cannot_delete_or_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => 'student',
        ])->assertSessionHasErrors('role');

        $this->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_students_cannot_create_users(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('admin.users.store'), [
                'name' => 'Unauthorized',
                'email' => 'unauthorized@example.com',
                'password' => 'password-123',
                'password_confirmation' => 'password-123',
                'role' => 'admin',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_demo_login_screen_can_be_rendered()
    {
        $this->get(route('demo.login'))
            ->assertOk()
            ->assertSee('Remember me');
    }

    public function test_demo_login_authenticates_an_active_user()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Admin@12345',
            'status' => true,
            'role' => 'admin',
        ]);

        $response = $this->post(route('demo.login.authenticate'), [
            'email' => $user->email,
            'password' => 'Admin@12345',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame('', $user->fresh()->getRememberToken());
    }

    public function test_seeded_administrator_can_log_in()
    {
        $previousPassword = getenv('ADMIN_PASSWORD');
        $hadPassword = $previousPassword !== false;
        $previousEnvironmentValue = $_ENV['ADMIN_PASSWORD'] ?? null;
        $previousServerValue = $_SERVER['ADMIN_PASSWORD'] ?? null;

        putenv('ADMIN_PASSWORD=Seeded-Admin-Test-Password');
        $_ENV['ADMIN_PASSWORD'] = 'Seeded-Admin-Test-Password';
        $_SERVER['ADMIN_PASSWORD'] = 'Seeded-Admin-Test-Password';

        try {
            $this->seed();
        } finally {
            if ($hadPassword) {
                putenv("ADMIN_PASSWORD={$previousPassword}");
            } else {
                putenv('ADMIN_PASSWORD');
            }

            if ($previousEnvironmentValue === null) {
                unset($_ENV['ADMIN_PASSWORD']);
            } else {
                $_ENV['ADMIN_PASSWORD'] = $previousEnvironmentValue;
            }

            if ($previousServerValue === null) {
                unset($_SERVER['ADMIN_PASSWORD']);
            } else {
                $_SERVER['ADMIN_PASSWORD'] = $previousServerValue;
            }
        }

        $response = $this->post(route('demo.login.authenticate'), [
            'email' => 'admin@example.com',
            'password' => 'Seeded-Admin-Test-Password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', 'admin@example.com')->firstOrFail());
    }

    public function test_demo_login_rejects_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Admin@12345',
        ]);

        $response = $this->from(route('demo.login'))->post(route('demo.login.authenticate'), [
            'email' => 'admin@example.com',
            'password' => 'incorrect',
        ]);

        $response->assertRedirect(route('demo.login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_cannot_use_demo_login()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'Admin@12345',
            'status' => false,
        ]);

        $this->from(route('demo.login'))
            ->post(route('demo.login.authenticate'), [
                'email' => 'admin@example.com',
                'password' => 'Admin@12345',
            ])
            ->assertRedirect(route('demo.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_can_log_out_and_return_to_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('demo.logout'));

        $response->assertRedirect(route('demo.login'));
        $this->assertGuest();
    }

    public function test_admin_page_requires_authentication()
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_verified_users_can_view_admin_page_and_see_logout_form()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('Admin.admin')
            ->assertViewHas('users');
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_users_are_rate_limited()
    {
        $user = User::factory()->create();

        RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }
}

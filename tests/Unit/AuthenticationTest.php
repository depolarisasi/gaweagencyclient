<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** @test */
    public function user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+62812345678',
            'company_name' => 'Test Company'
        ];

        $response = $this->post('/register', $userData);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+62812345678',
            'company_name' => 'Test Company',
            'role' => 'client',
            'status' => 'active'
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
        $response->assertRedirect('/client/dashboard');
    }

    /** @test */
    public function user_cannot_register_with_invalid_email()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => 'invalid-email']);
    }

    /** @test */
    public function user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    /** @test */
    public function user_cannot_register_with_mismatched_passwords()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticatedAs($user);
        
        // Check redirect based on role
        if ($user->role === 'admin') {
            $response->assertRedirect('/admin/dashboard');
        } elseif ($user->role === 'staff') {
            $response->assertRedirect('/staff/dashboard');
        } else {
            $response->assertRedirect('/client/dashboard');
        }
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function inactive_user_cannot_login()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive'
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /** @test */
    public function user_can_request_password_reset()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/password/email', [
            'email' => 'test@example.com'
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
        $response->assertSessionHas('status');
    }

    /** @test */
    public function user_cannot_request_password_reset_for_nonexistent_email()
    {
        $response = $this->post('/password/email', [
            'email' => 'nonexistent@example.com'
        ]);

        Notification::assertNothingSent();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->post('/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123'
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password123', $user->password));
        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_cannot_reset_password_with_invalid_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $oldPassword = $user->password;

        $response = $this->post('/password/reset', [
            'token' => 'invalid_token',
            'email' => 'test@example.com',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123'
        ]);

        $user->refresh();
        $this->assertEquals($oldPassword, $user->password);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function admin_user_redirects_to_admin_dashboard_after_login()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin/dashboard');
    }

    /** @test */
    public function staff_user_redirects_to_staff_dashboard_after_login()
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'staff@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticatedAs($staff);
        $response->assertRedirect('/staff/dashboard');
    }

    /** @test */
    public function client_user_redirects_to_client_dashboard_after_login()
    {
        $client = User::factory()->create([
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'status' => 'active'
        ]);

        $response = $this->post('/login', [
            'email' => 'client@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticatedAs($client);
        $response->assertRedirect('/client/dashboard');
    }

    /** @test */
    public function user_registration_sets_default_role_and_status()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $this->post('/register', $userData);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertEquals('client', $user->role);
        $this->assertEquals('active', $user->status);
    }

    /** @test */
    public function user_login_updates_last_login_timestamp()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'last_login_at' => null
        ]);

        $this->assertNull($user->last_login_at);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertTrue($user->last_login_at->isToday());
    }

    /** @test */
    public function guest_user_cannot_access_protected_routes()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/staff/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/client/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_cannot_access_auth_pages()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/login');
        $response->assertRedirect('/dashboard');

        $response = $this->get('/register');
        $response->assertRedirect('/dashboard');
    }
}
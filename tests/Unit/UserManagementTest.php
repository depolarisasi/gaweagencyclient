<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->client = User::factory()->create(['role' => 'client']);
    }

    /** @test */
    public function admin_can_view_users_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    /** @test */
    public function non_admin_cannot_access_user_management()
    {
        $response = $this->actingAs($this->staff)
            ->get('/admin/users');
        $response->assertStatus(403);

        $response = $this->actingAs($this->client)
            ->get('/admin/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'client',
            'status' => 'active',
            'phone' => '+62812345678',
            'company_name' => 'Test Company',
            'address' => '123 Test Street'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', $userData);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'client',
            'status' => 'active',
            'phone' => '+62812345678',
            'company_name' => 'Test Company',
            'address' => '123 Test Street'
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /** @test */
    public function admin_cannot_create_user_with_invalid_data()
    {
        $userData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'role' => 'invalid_role',
            'status' => 'invalid_status'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', $userData);

        $response->assertSessionHasErrors([
            'name', 'email', 'password', 'role', 'status'
        ]);
    }

    /** @test */
    public function admin_can_view_user_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
        $response->assertViewHas('user', $user);
    }

    /** @test */
    public function admin_can_edit_user()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'client'
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user', $user);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'client',
            'status' => 'active'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'staff',
            'status' => 'inactive',
            'phone' => '+62812345678',
            'company_name' => 'Updated Company',
            'address' => 'Updated Address'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", $updateData);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'staff',
            'status' => 'inactive',
            'phone' => '+62812345678',
            'company_name' => 'Updated Company',
            'address' => 'Updated Address'
        ]);

        $response->assertRedirect('/admin/users');
    }

    /** @test */
    public function admin_can_update_user_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password')
        ]);

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", $updateData);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password123', $user->password));
        $this->assertFalse(Hash::check('old_password', $user->password));
    }

    /** @test */
    public function admin_can_update_user_without_changing_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('original_password')
        ]);
        $originalPassword = $user->password;

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", $updateData);

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
        $this->assertEquals('Updated Name', $user->name);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$user->id}");

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $response->assertRedirect('/admin/users');
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$this->admin->id}");

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_toggle_user_status()
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/toggle-status");

        $user->refresh();
        $this->assertEquals('inactive', $user->status);

        // Toggle back
        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/toggle-status");

        $user->refresh();
        $this->assertEquals('active', $user->status);
    }

    /** @test */
    public function admin_can_perform_bulk_actions()
    {
        $users = User::factory()->count(3)->create(['status' => 'active']);
        $userIds = $users->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->post('/admin/users/bulk-action', [
                'action' => 'deactivate',
                'user_ids' => $userIds
            ]);

        foreach ($users as $user) {
            $user->refresh();
            $this->assertEquals('inactive', $user->status);
        }
    }

    /** @test */
    public function admin_can_search_users()
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users?search=John');

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    /** @test */
    public function admin_can_filter_users_by_role()
    {
        User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        User::factory()->create(['role' => 'client', 'name' => 'Client User']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users?role=admin');

        $response->assertStatus(200);
        $response->assertSee('Admin User');
        $response->assertDontSee('Client User');
    }

    /** @test */
    public function admin_can_filter_users_by_status()
    {
        User::factory()->create(['status' => 'active', 'name' => 'Active User']);
        User::factory()->create(['status' => 'inactive', 'name' => 'Inactive User']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/users?status=active');

        $response->assertStatus(200);
        $response->assertSee('Active User');
        $response->assertDontSee('Inactive User');
    }

    /** @test */
    public function user_model_has_correct_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $expectedFillable = [
            'name', 'email', 'password', 'role', 'status',
            'phone', 'address', 'company_name', 'last_login_at'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function user_model_has_correct_hidden_attributes()
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /** @test */
    public function user_model_has_correct_casts()
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('datetime', $casts['last_login_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    /** @test */
    public function user_helper_methods_work_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $client = User::factory()->create(['role' => 'client']);
        $activeUser = User::factory()->create(['status' => 'active']);
        $inactiveUser = User::factory()->create(['status' => 'inactive']);

        // Test isAdmin()
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($staff->isAdmin());
        $this->assertFalse($client->isAdmin());

        // Test isStaff()
        $this->assertTrue($admin->isStaff());
        $this->assertTrue($staff->isStaff());
        $this->assertFalse($client->isStaff());

        // Test isClient()
        $this->assertFalse($admin->isClient());
        $this->assertFalse($staff->isClient());
        $this->assertTrue($client->isClient());

        // Test isActive()
        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
    }

    /** @test */
    public function user_relationships_work_correctly()
    {
        $user = User::factory()->create();

        // Test that relationships are defined
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->orders());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->projects());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->invoices());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->supportTickets());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->assignedProjects());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Relations\HasMany', $user->assignedTickets());
    }
}
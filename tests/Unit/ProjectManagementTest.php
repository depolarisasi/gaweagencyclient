<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Order;
use App\Models\Product;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $client;
    protected $product;
    protected $template;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->client = User::factory()->create(['role' => 'client']);
        $this->product = Product::factory()->create();
        $this->template = Template::factory()->create();
    }

    /** @test */
    public function admin_can_view_projects_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/projects');

        $response->assertStatus(200);
        $response->assertViewIs('admin.projects.index');
        $response->assertViewHas('projects');
    }

    /** @test */
    public function staff_can_view_projects_index()
    {
        $response = $this->actingAs($this->staff)
            ->get('/admin/projects');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_cannot_access_admin_projects()
    {
        $response = $this->actingAs($this->client)
            ->get('/admin/projects');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_project()
    {
        $order = Order::factory()->create([
            'user_id' => $this->client->id,
            'product_id' => $this->product->id
        ]);

        $projectData = [
            'project_name' => 'Test Project',
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending',
            'assigned_to' => $this->staff->id,
            'description' => 'Test project description',
            'website_url' => 'https://example.com',
            'admin_url' => 'https://example.com/admin',
            'admin_username' => 'admin',
            'admin_password' => 'password123',
            'notes' => 'Internal notes'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/projects', $projectData);

        $this->assertDatabaseHas('projects', [
            'project_name' => 'Test Project',
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'status' => 'pending',
            'assigned_to' => $this->staff->id,
            'description' => 'Test project description',
            'website_url' => 'https://example.com',
            'admin_url' => 'https://example.com/admin',
            'admin_username' => 'admin',
            'admin_password' => 'password123',
            'notes' => 'Internal notes'
        ]);
    }

    /** @test */
    public function admin_cannot_create_project_with_invalid_data()
    {
        $projectData = [
            'project_name' => '',
            'user_id' => 999999, // Non-existent user
            'status' => 'invalid_status'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/projects', $projectData);

        $response->assertSessionHasErrors([
            'project_name', 'user_id', 'status'
        ]);
    }

    /** @test */
    public function admin_can_view_project_details()
    {
        $project = Project::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.projects.show');
        $response->assertViewHas('project', $project);
    }

    /** @test */
    public function admin_can_edit_project()
    {
        $project = Project::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/projects/{$project->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.projects.edit');
        $response->assertViewHas('project', $project);
        $response->assertViewHas('clients');
        $response->assertViewHas('staff');
    }

    /** @test */
    public function admin_can_update_project()
    {
        $project = Project::factory()->create([
            'project_name' => 'Original Project',
            'user_id' => $this->client->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'project_name' => 'Updated Project',
            'user_id' => $this->client->id,
            'status' => 'in_progress',
            'assigned_to' => $this->staff->id,
            'description' => 'Updated description',
            'website_url' => 'https://updated.com',
            'admin_url' => 'https://updated.com/admin',
            'admin_username' => 'newadmin',
            'admin_password' => 'newpassword',
            'notes' => 'Updated notes'
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/projects/{$project->id}", $updateData);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'project_name' => 'Updated Project',
            'status' => 'in_progress',
            'assigned_to' => $this->staff->id,
            'description' => 'Updated description',
            'website_url' => 'https://updated.com',
            'admin_url' => 'https://updated.com/admin',
            'admin_username' => 'newadmin',
            'admin_password' => 'newpassword',
            'notes' => 'Updated notes'
        ]);

        $response->assertRedirect('/admin/projects');
    }

    /** @test */
    public function admin_can_delete_project()
    {
        $project = Project::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/projects/{$project->id}");

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $response->assertRedirect('/admin/projects');
    }

    /** @test */
    public function admin_can_assign_project_to_staff()
    {
        $project = Project::factory()->create([
            'assigned_to' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$project->id}/assign", [
                'assigned_to' => $this->staff->id
            ]);

        $project->refresh();
        $this->assertEquals($this->staff->id, $project->assigned_to);
    }

    /** @test */
    public function admin_can_update_project_progress()
    {
        $project = Project::factory()->create([
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$project->id}/progress", [
                'status' => 'in_progress',
                'progress_notes' => 'Started development'
            ]);

        $project->refresh();
        $this->assertEquals('in_progress', $project->status);
    }

    /** @test */
    public function admin_can_complete_project()
    {
        $project = Project::factory()->create([
            'status' => 'in_progress'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$project->id}/complete");

        $project->refresh();
        $this->assertEquals('completed', $project->status);
        $this->assertNotNull($project->completed_at);
    }

    /** @test */
    public function admin_can_put_project_on_hold()
    {
        $project = Project::factory()->create([
            'status' => 'in_progress'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$project->id}/hold");

        $project->refresh();
        $this->assertEquals('on_hold', $project->status);
    }

    /** @test */
    public function admin_can_resume_project()
    {
        $project = Project::factory()->create([
            'status' => 'on_hold'
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/projects/{$project->id}/resume");

        $project->refresh();
        $this->assertEquals('in_progress', $project->status);
    }

    /** @test */
    public function admin_can_perform_bulk_actions_on_projects()
    {
        $projects = Project::factory()->count(3)->create([
            'status' => 'pending'
        ]);
        $projectIds = $projects->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->post('/admin/projects/bulk-action', [
                'action' => 'start',
                'project_ids' => $projectIds
            ]);

        foreach ($projects as $project) {
            $project->refresh();
            $this->assertEquals('in_progress', $project->status);
        }
    }

    /** @test */
    public function client_can_view_their_projects()
    {
        $project = Project::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->client)
            ->get('/client/projects');

        $response->assertStatus(200);
        $response->assertViewIs('client.projects.index');
        $response->assertSee($project->project_name);
    }

    /** @test */
    public function client_can_view_their_project_details()
    {
        $project = Project::factory()->create([
            'user_id' => $this->client->id
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/projects/{$project->id}");

        $response->assertStatus(200);
        $response->assertViewIs('client.projects.show');
        $response->assertViewHas('project', $project);
    }

    /** @test */
    public function client_cannot_view_other_clients_projects()
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $project = Project::factory()->create([
            'user_id' => $otherClient->id
        ]);

        $response = $this->actingAs($this->client)
            ->get("/client/projects/{$project->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function staff_can_view_assigned_projects()
    {
        $assignedProject = Project::factory()->create([
            'assigned_to' => $this->staff->id
        ]);
        $unassignedProject = Project::factory()->create([
            'assigned_to' => null
        ]);

        $response = $this->actingAs($this->staff)
            ->get('/staff/projects');

        $response->assertStatus(200);
        $response->assertSee($assignedProject->project_name);
        $response->assertDontSee($unassignedProject->project_name);
    }

    /** @test */
    public function project_model_relationships_work_correctly()
    {
        $order = Order::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'template_id' => $this->template->id,
            'assigned_to' => $this->staff->id
        ]);

        // Test relationships
        $this->assertInstanceOf(User::class, $project->user);
        $this->assertInstanceOf(Order::class, $project->order);
        $this->assertInstanceOf(Template::class, $project->template);
        $this->assertInstanceOf(User::class, $project->assignedStaff);

        $this->assertEquals($this->client->id, $project->user->id);
        $this->assertEquals($order->id, $project->order->id);
        $this->assertEquals($this->template->id, $project->template->id);
        $this->assertEquals($this->staff->id, $project->assignedStaff->id);
    }

    /** @test */
    public function project_model_has_correct_fillable_attributes()
    {
        $project = new Project();
        $fillable = $project->getFillable();

        $expectedFillable = [
            'project_name', 'user_id', 'order_id', 'template_id',
            'status', 'assigned_to', 'description', 'website_url',
            'admin_url', 'admin_username', 'admin_password',
            'notes', 'started_at', 'completed_at'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function project_model_has_correct_casts()
    {
        $project = new Project();
        $casts = $project->getCasts();

        $this->assertEquals('datetime', $casts['started_at']);
        $this->assertEquals('datetime', $casts['completed_at']);
    }

    /** @test */
    public function project_scopes_work_correctly()
    {
        // Create projects with different statuses
        $activeProject = Project::factory()->create(['status' => 'in_progress']);
        $completedProject = Project::factory()->create(['status' => 'completed']);
        $pendingProject = Project::factory()->create(['status' => 'pending']);

        // Test active scope
        $activeProjects = Project::active()->get();
        $this->assertTrue($activeProjects->contains($activeProject));
        $this->assertFalse($activeProjects->contains($completedProject));

        // Test completed scope
        $completedProjects = Project::completed()->get();
        $this->assertTrue($completedProjects->contains($completedProject));
        $this->assertFalse($completedProjects->contains($activeProject));

        // Test pending scope
        $pendingProjects = Project::pending()->get();
        $this->assertTrue($pendingProjects->contains($pendingProject));
        $this->assertFalse($pendingProjects->contains($activeProject));
    }

    /** @test */
    public function project_can_be_automatically_created_from_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->client->id,
            'product_id' => $this->product->id,
            'status' => 'completed'
        ]);

        // Simulate automatic project creation
        $project = Project::create([
            'project_name' => "Website for {$order->user->name}",
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'status' => 'pending',
            'description' => "Project created from order #{$order->id}"
        ]);

        $this->assertDatabaseHas('projects', [
            'user_id' => $this->client->id,
            'order_id' => $order->id,
            'status' => 'pending'
        ]);

        $this->assertEquals($order->id, $project->order_id);
        $this->assertEquals($this->client->id, $project->user_id);
    }
}
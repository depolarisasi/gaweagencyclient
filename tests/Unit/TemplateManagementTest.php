<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Template;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TemplateManagementTest extends TestCase
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
    public function admin_can_view_templates_index()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/templates');

        $response->assertStatus(200);
        $response->assertViewIs('admin.templates.index');
        $response->assertViewHas('templates');
    }

    /** @test */
    public function staff_can_view_templates_index()
    {
        $response = $this->actingAs($this->staff)
            ->get('/admin/templates');

        $response->assertStatus(200);
    }

    /** @test */
    public function client_cannot_access_admin_templates()
    {
        $response = $this->actingAs($this->client)
            ->get('/admin/templates');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_template()
    {
        $templateData = [
            'name' => 'Business Pro Template',
            'description' => 'Professional business website template',
            'category' => 'business',
            'demo_url' => 'https://demo.example.com/business-pro',
            'thumbnail_url' => 'https://example.com/thumbnails/business-pro.jpg',
            'features' => ['Responsive Design', 'SEO Optimized', 'Contact Form', 'Gallery'],
            'is_active' => true,
            'sort_order' => 1
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $this->assertDatabaseHas('templates', [
            'name' => 'Business Pro Template',
            'description' => 'Professional business website template',
            'category' => 'business',
            'demo_url' => 'https://demo.example.com/business-pro',
            'thumbnail_url' => 'https://example.com/thumbnails/business-pro.jpg',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $template = Template::where('name', 'Business Pro Template')->first();
        $this->assertEquals(['Responsive Design', 'SEO Optimized', 'Contact Form', 'Gallery'], $template->features);
    }

    /** @test */
    public function admin_cannot_create_template_with_invalid_data()
    {
        $templateData = [
            'name' => '',
            'description' => '',
            'category' => 'invalid_category',
            'demo_url' => 'invalid-url',
            'is_active' => 'invalid_boolean'
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $response->assertSessionHasErrors([
            'name', 'description', 'category', 'demo_url'
        ]);
    }

    /** @test */
    public function admin_can_view_template_details()
    {
        $template = Template::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.templates.show');
        $response->assertViewHas('template', $template);
    }

    /** @test */
    public function admin_can_edit_template()
    {
        $template = Template::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/templates/{$template->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('admin.templates.edit');
        $response->assertViewHas('template', $template);
    }

    /** @test */
    public function admin_can_update_template()
    {
        $template = Template::factory()->create([
            'name' => 'Original Template',
            'category' => 'business',
            'is_active' => true
        ]);

        $updateData = [
            'name' => 'Updated Template',
            'description' => 'Updated description',
            'category' => 'ecommerce',
            'demo_url' => 'https://updated-demo.example.com',
            'thumbnail_url' => 'https://example.com/updated-thumbnail.jpg',
            'features' => ['Updated Feature 1', 'Updated Feature 2'],
            'is_active' => false,
            'sort_order' => 5
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/templates/{$template->id}", $updateData);

        $this->assertDatabaseHas('templates', [
            'id' => $template->id,
            'name' => 'Updated Template',
            'description' => 'Updated description',
            'category' => 'ecommerce',
            'demo_url' => 'https://updated-demo.example.com',
            'thumbnail_url' => 'https://example.com/updated-thumbnail.jpg',
            'is_active' => false,
            'sort_order' => 5
        ]);

        $template->refresh();
        $this->assertEquals(['Updated Feature 1', 'Updated Feature 2'], $template->features);

        $response->assertRedirect('/admin/templates');
    }

    /** @test */
    public function admin_can_delete_template()
    {
        $template = Template::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/templates/{$template->id}");

        $this->assertDatabaseMissing('templates', ['id' => $template->id]);
        $response->assertRedirect('/admin/templates');
    }

    /** @test */
    public function admin_can_toggle_template_status()
    {
        $template = Template::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/toggle-status");

        $template->refresh();
        $this->assertFalse($template->is_active);

        // Toggle back
        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/toggle-status");

        $template->refresh();
        $this->assertTrue($template->is_active);
    }

    /** @test */
    public function admin_can_duplicate_template()
    {
        $template = Template::factory()->create([
            'name' => 'Original Template',
            'features' => ['Feature 1', 'Feature 2']
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/templates/{$template->id}/duplicate");

        $this->assertDatabaseHas('templates', [
            'name' => 'Original Template (Copy)',
            'description' => $template->description,
            'category' => $template->category,
            'is_active' => false // Duplicates should be inactive by default
        ]);

        $duplicatedTemplate = Template::where('name', 'Original Template (Copy)')->first();
        $this->assertEquals($template->features, $duplicatedTemplate->features);
    }

    /** @test */
    public function admin_can_update_sort_order()
    {
        $template1 = Template::factory()->create(['sort_order' => 1]);
        $template2 = Template::factory()->create(['sort_order' => 2]);
        $template3 = Template::factory()->create(['sort_order' => 3]);

        $sortData = [
            'sort_order' => [
                $template3->id => 1,
                $template1->id => 2,
                $template2->id => 3
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates/sort-order', $sortData);

        $template1->refresh();
        $template2->refresh();
        $template3->refresh();

        $this->assertEquals(2, $template1->sort_order);
        $this->assertEquals(3, $template2->sort_order);
        $this->assertEquals(1, $template3->sort_order);
    }

    /** @test */
    public function admin_can_perform_bulk_actions()
    {
        $templates = Template::factory()->count(3)->create(['is_active' => true]);
        $templateIds = $templates->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates/bulk-action', [
                'action' => 'deactivate',
                'template_ids' => $templateIds
            ]);

        foreach ($templates as $template) {
            $template->refresh();
            $this->assertFalse($template->is_active);
        }
    }

    /** @test */
    public function admin_can_search_templates()
    {
        Template::factory()->create(['name' => 'Business Template']);
        Template::factory()->create(['name' => 'E-commerce Template']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/templates-search?q=Business');

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Business Template']);
        $response->assertJsonMissing(['name' => 'E-commerce Template']);
    }

    /** @test */
    public function admin_can_get_template_statistics()
    {
        Template::factory()->count(5)->create(['is_active' => true]);
        Template::factory()->count(3)->create(['is_active' => false]);
        Template::factory()->count(2)->create(['category' => 'business']);
        Template::factory()->count(3)->create(['category' => 'ecommerce']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/templates-statistics');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_templates',
            'active_templates',
            'inactive_templates',
            'categories'
        ]);
    }

    /** @test */
    public function clients_can_view_active_templates()
    {
        $activeTemplate = Template::factory()->create(['is_active' => true]);
        $inactiveTemplate = Template::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->client)
            ->get('/templates');

        $response->assertStatus(200);
        $response->assertSee($activeTemplate->name);
        $response->assertDontSee($inactiveTemplate->name);
    }

    /** @test */
    public function clients_can_view_template_details()
    {
        $template = Template::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->client)
            ->get("/templates/{$template->id}");

        $response->assertStatus(200);
        $response->assertViewIs('templates.show');
        $response->assertViewHas('template', $template);
    }

    /** @test */
    public function clients_cannot_view_inactive_template_details()
    {
        $template = Template::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->client)
            ->get("/templates/{$template->id}");

        $response->assertStatus(404);
    }

    /** @test */
    public function template_model_relationships_work_correctly()
    {
        $template = Template::factory()->create();
        $project = Project::factory()->create(['template_id' => $template->id]);

        // Test relationships
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $template->projects);
        $this->assertTrue($template->projects->contains($project));
    }

    /** @test */
    public function template_model_has_correct_fillable_attributes()
    {
        $template = new Template();
        $fillable = $template->getFillable();

        $expectedFillable = [
            'name', 'description', 'category', 'demo_url',
            'thumbnail_url', 'features', 'is_active', 'sort_order'
        ];

        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $fillable);
        }
    }

    /** @test */
    public function template_model_has_correct_casts()
    {
        $template = new Template();
        $casts = $template->getCasts();

        $this->assertEquals('array', $casts['features']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('integer', $casts['sort_order']);
    }

    /** @test */
    public function template_scopes_work_correctly()
    {
        $activeTemplate = Template::factory()->create(['is_active' => true]);
        $inactiveTemplate = Template::factory()->create(['is_active' => false]);
        $businessTemplate = Template::factory()->create(['category' => 'business']);
        $ecommerceTemplate = Template::factory()->create(['category' => 'ecommerce']);

        // Test active scope
        $activeTemplates = Template::active()->get();
        $this->assertTrue($activeTemplates->contains($activeTemplate));
        $this->assertFalse($activeTemplates->contains($inactiveTemplate));

        // Test by category scope
        $businessTemplates = Template::byCategory('business')->get();
        $this->assertTrue($businessTemplates->contains($businessTemplate));
        $this->assertFalse($businessTemplates->contains($ecommerceTemplate));

        // Test ordered scope
        $template1 = Template::factory()->create(['sort_order' => 2]);
        $template2 = Template::factory()->create(['sort_order' => 1]);
        $template3 = Template::factory()->create(['sort_order' => 3]);

        $orderedTemplates = Template::ordered()->get();
        $this->assertEquals($template2->id, $orderedTemplates->first()->id);
        $this->assertEquals($template3->id, $orderedTemplates->last()->id);
    }

    /** @test */
    public function template_categories_are_validated()
    {
        $validCategories = ['business', 'ecommerce', 'portfolio', 'blog', 'landing'];
        
        foreach ($validCategories as $category) {
            $template = Template::factory()->create(['category' => $category]);
            $this->assertEquals($category, $template->category);
        }
    }

    /** @test */
    public function template_features_are_stored_as_json_array()
    {
        $features = ['Responsive Design', 'SEO Optimized', 'Contact Form', 'Gallery', 'Blog Section'];
        
        $template = Template::factory()->create([
            'features' => $features
        ]);

        $this->assertEquals($features, $template->features);
        $this->assertIsArray($template->features);
        $this->assertCount(5, $template->features);
    }

    /** @test */
    public function template_sort_order_affects_display_order()
    {
        $template1 = Template::factory()->create(['sort_order' => 3, 'name' => 'Third']);
        $template2 = Template::factory()->create(['sort_order' => 1, 'name' => 'First']);
        $template3 = Template::factory()->create(['sort_order' => 2, 'name' => 'Second']);

        $sortedTemplates = Template::orderBy('sort_order')->get();
        
        $this->assertEquals('First', $sortedTemplates[0]->name);
        $this->assertEquals('Second', $sortedTemplates[1]->name);
        $this->assertEquals('Third', $sortedTemplates[2]->name);
    }

    /** @test */
    public function template_can_be_used_in_projects()
    {
        $template = Template::factory()->create(['is_active' => true]);
        $project = Project::factory()->create(['template_id' => $template->id]);

        $this->assertEquals($template->id, $project->template_id);
        $this->assertTrue($template->projects->contains($project));
    }

    /** @test */
    public function template_demo_url_validation_works()
    {
        $templateData = [
            'name' => 'Test Template',
            'description' => 'Test description',
            'category' => 'business',
            'demo_url' => 'invalid-url',
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $response->assertSessionHasErrors('demo_url');
    }

    /** @test */
    public function template_thumbnail_url_validation_works()
    {
        $templateData = [
            'name' => 'Test Template',
            'description' => 'Test description',
            'category' => 'business',
            'thumbnail_url' => 'invalid-url',
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $response->assertSessionHasErrors('thumbnail_url');
    }

    /** @test */
    public function template_name_must_be_unique()
    {
        Template::factory()->create(['name' => 'Unique Template']);

        $templateData = [
            'name' => 'Unique Template',
            'description' => 'Test description',
            'category' => 'business',
            'is_active' => true
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/templates', $templateData);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function template_can_have_empty_features_array()
    {
        $template = Template::factory()->create(['features' => []]);

        $this->assertEquals([], $template->features);
        $this->assertIsArray($template->features);
        $this->assertCount(0, $template->features);
    }

    /** @test */
    public function template_sort_order_defaults_to_zero()
    {
        $template = Template::factory()->create(['sort_order' => null]);

        $this->assertEquals(0, $template->sort_order);
    }
}
<?php

namespace Tests\Feature\Public;

use App\Models\Blog;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PublicRoutesSmokeTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // layouts/web/partials/header accede a headings.header_btn_link sin ??,
        // por lo que toda vista publica requiere la Option manage-pages
        \App\Models\Option::factory()->create([
            'key' => 'manage-pages',
            'value' => ['headings' => ['header_btn_link' => 'login']],
        ]);
    }

    public function test_home_renders(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_blogs_index_renders(): void
    {
        // Arrange
        Blog::factory()->create();

        // Act + Assert
        $this->get('/blogs')->assertOk();
    }

    public function test_blog_show_renders(): void
    {
        // Arrange
        $blog = Blog::factory()->create();

        // Act + Assert
        $this->get("/blogs/{$blog->slug}")->assertOk();
    }

    public function test_plans_index_renders(): void
    {
        // Arrange
        Plan::factory()->count(2)->create();

        // Act + Assert
        $this->get('/plans')->assertOk();
    }

    public function test_about_us_renders(): void
    {
        $this->get('/about-us')->assertOk();
    }

    public function test_terms_conditions_renders(): void
    {
        $this->get('/terms-conditions')->assertOk();
    }

    public function test_privacy_policy_renders(): void
    {
        $this->get('/privacy-policy')->assertOk();
    }

    public function test_contact_us_renders(): void
    {
        $this->get('/contact-us')->assertOk();
    }

    public function test_contact_store_creates_message(): void
    {
        // Act
        $response = $this->postJson('/contact/store', [
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.test',
            'company_name' => 'ACME',
            'message' => 'Hello, I need information about plans.',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('message', __('Your Message Submitted successfully'));

        $this->assertDatabaseHas('messages', [
            'email' => 'john@example.test',
        ]);
    }

    public function test_filter_blogs_by_tag_returns_200(): void
    {
        // Arrange
        Blog::factory()->create(['tags' => ['news']]);

        // Act + Assert
        $this->getJson('/filter-blogs-by-tag?tag=news')
            ->assertOk()
            ->assertJsonStructure(['blogs']);
    }
}

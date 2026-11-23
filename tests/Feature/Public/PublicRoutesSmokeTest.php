<?php

namespace Tests\Feature\Public;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Option;
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
        Option::factory()->create([
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

    // Pub1 — spec-public-writes-throttle-fase5: comentario válido con blog_slug basura
    public function test_pub1_valid_comment_redirects_with_real_blog_slug(): void
    {
        // Arrange
        $blog = Blog::factory()->create();

        // Act
        $response = $this->postJson('/blogs', [
            'name' => 'John Doe',
            'email' => 'john@example.test',
            'comment' => 'Great article!',
            'blog_id' => $blog->id,
            'blog_slug' => 'client-sent-garbage',
        ]);

        // Assert: redirect derivado del slug real, no del input del cliente
        $response->assertOk()
            ->assertJsonPath('redirect', route('blogs.show', $blog->slug));

        $this->assertDatabaseHas('comments', [
            'blog_id' => $blog->id,
            'email' => 'john@example.test',
            'status' => 1,
        ]);
    }

    // Pub2 — throttle: 6.º comentario devuelve 429
    public function test_pub2_comment_is_throttled_after_five_posts(): void
    {
        // Arrange
        $blog = Blog::factory()->create();
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.test',
            'comment' => 'Great article!',
            'blog_id' => $blog->id,
        ];

        // Act + Assert: 5 intentos válidos
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/blogs', $payload)->assertOk();
        }

        // 6.º intento dentro del mismo minuto → 429
        $this->postJson('/blogs', $payload)
            ->assertStatus(429)
            ->assertHeader('Retry-After');
    }

    // Pub3 — blog_id inexistente → 422 (antes 500 por FK)
    public function test_pub3_comment_with_nonexistent_blog_id_returns_422(): void
    {
        // Act
        $response = $this->postJson('/blogs', [
            'name' => 'John Doe',
            'email' => 'john@example.test',
            'comment' => 'Great article!',
            'blog_id' => 999,
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['blog_id']);

        $this->assertDatabaseMissing('comments', ['blog_id' => 999]);
    }

    // Pub4 — campos ausentes → 422 con todos los errores
    public function test_pub4_comment_with_missing_fields_returns_422(): void
    {
        // Act + Assert
        $this->postJson('/blogs', [])->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'comment', 'blog_id']);
    }

    // Pub5 — email sobre el límite de columna (string(255)) → 422
    public function test_pub5_comment_with_oversized_email_returns_422(): void
    {
        // Arrange
        $blog = Blog::factory()->create();

        // Act + Assert
        $this->postJson('/blogs', [
            'name' => 'John Doe',
            'email' => str_repeat('a', 250).'@x.com',
            'comment' => 'Great article!',
            'blog_id' => $blog->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('comments', 0);
    }

    // Pub6 — throttle: 6.º mensaje de contacto devuelve 429
    public function test_pub6_contact_message_is_throttled_after_five_posts(): void
    {
        // Arrange
        $payload = [
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.test',
            'message' => 'Hello, I need information about plans.',
        ];

        // Act + Assert: 5 intentos válidos
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/contact/store', $payload)->assertOk();
        }

        // 6.º intento dentro del mismo minuto → 429
        $this->postJson('/contact/store', $payload)
            ->assertStatus(429)
            ->assertHeader('Retry-After');
    }

    // Pub7 — APP_ENV=demo bloquea ambos writes públicos
    public function test_pub7_demo_blocks_public_writes(): void
    {
        // Arrange
        // env=demo desactiva runningUnitTests() → CSRF exige token; se salta
        // igual que en DemoModeTest (F2/F3 del spec 3.5)
        app()->detectEnvironment(fn () => 'demo');
        $blog = Blog::factory()->create();

        // Act + Assert
        $this->withoutMiddleware(PreventRequestForgery::class)->postJson('/blogs', [
            'name' => 'John Doe',
            'email' => 'john@example.test',
            'comment' => 'Great article!',
            'blog_id' => $blog->id,
        ])->assertStatus(499)
            ->assertJson(['message' => 'This action is disabled in demo mode.']);

        $this->postJson('/contact/store', [
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.test',
            'message' => 'Hello, I need information about plans.',
        ])->assertStatus(499)
            ->assertJson(['message' => 'This action is disabled in demo mode.']);

        // 0 filas creadas
        $this->assertDatabaseCount('comments', 0);
        $this->assertDatabaseCount('messages', 0);
    }

    // Pub8 — spec-moderacion-comentarios-fase5: el whereStatus(1) del front
    // se vuelve el contrato de moderación — el comentario ocultado por el
    // admin no renderiza; el aprobado sí.
    public function test_pub8_hidden_comment_is_not_rendered_on_blog_show(): void
    {
        // Arrange
        $blog = Blog::factory()->create();
        Comment::factory()->create([
            'blog_id' => $blog->id,
            'status' => 1,
            'comment' => 'Visible approved comment',
        ]);
        Comment::factory()->create([
            'blog_id' => $blog->id,
            'status' => 0,
            'comment' => 'Hidden spam comment',
        ]);

        // Act
        $response = $this->get("/blogs/{$blog->slug}");

        // Assert
        $response->assertOk();
        $this->assertStringContainsString('Visible approved comment', $response->getContent());
        $this->assertStringNotContainsString('Hidden spam comment', $response->getContent());
    }
}

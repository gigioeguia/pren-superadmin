<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\PreventRequestForgery;
use App\Models\Blog;
use App\Models\Comment;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

/**
 * G1–G6 del spec-moderacion-comentarios-fase5 (Fase 5 / 5.19, Seg-30):
 * post-moderación — el admin oculta/re-aprueba desde el listador per-blog,
 * que ahora muestra TODO el inventario (status 0 y 1). Incluye los dos
 * D-FIX declarados: scope por blog_id en el filter (G4) y findOrFail
 * en destroy (G5).
 */
#[Group('smoke')]
class CommentModerationTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        app()->detectEnvironment(fn () => 'testing');

        parent::tearDown();
    }

    // G1 — ocultar: status 1→0, JSON verbatim {message: 'Comment'}
    public function test_g1_admin_hides_comment(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $comment = Comment::factory()->create(['status' => 1]);

        // Act
        $response = $this->actingAs($admin)
            ->postJson("/admin/comments/status/{$comment->id}", ['status' => 0]);

        // Assert
        $response->assertOk()->assertJson(['message' => 'Comment']);
        $this->assertSame(0, (int) $comment->fresh()->status);
    }

    // G2 — re-aprobar: status 0→1
    public function test_g2_admin_reapproves_comment(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $comment = Comment::factory()->create(['status' => 0]);

        // Act
        $response = $this->actingAs($admin)
            ->postJson("/admin/comments/status/{$comment->id}", ['status' => 1]);

        // Assert
        $response->assertOk()->assertJson(['message' => 'Comment']);
        $this->assertSame(1, (int) $comment->fresh()->status);
    }

    // G3 — regresión del listador: whereStatus(1) eliminado, ocultos visibles
    public function test_g3_lister_shows_hidden_comments(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $blog = Blog::factory()->create();
        Comment::factory()->create([
            'blog_id' => $blog->id,
            'status' => 0,
            'comment' => 'Spam hidden by admin',
        ]);

        // Act
        $response = $this->actingAs($admin)
            ->get(route('admin.blogs.filter.comment', $blog->id));

        // Assert
        $response->assertOk();
        $this->assertStringContainsString('Spam hidden by admin', $response->getContent());
    }

    // G4 — D-FIX-1: filter scopea con blog_id; sin input, contrato sin scope
    public function test_g4_filter_scopes_by_blog_id_and_preserves_contract_without_it(): void
    {
        // Arrange
        $admin = $this->createAdmin();
        $blogA = Blog::factory()->create();
        $blogB = Blog::factory()->create();
        Comment::factory()->create(['blog_id' => $blogA->id, 'comment' => 'Comment on blog A']);
        Comment::factory()->create(['blog_id' => $blogB->id, 'comment' => 'Comment on blog B']);

        // Act + Assert — con blog_id: solo filas del blog A
        $scoped = $this->actingAs($admin)->postJson('/admin/comments/filter', [
            'blog_id' => $blogA->id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $scoped->assertOk();
        $this->assertStringContainsString('Comment on blog A', $scoped->json('data'));
        $this->assertStringNotContainsString('Comment on blog B', $scoped->json('data'));

        // Sin blog_id: contrato actual preservado (sin scope)
        $unscoped = $this->actingAs($admin)
            ->postJson('/admin/comments/filter', [], ['X-Requested-With' => 'XMLHttpRequest']);

        $unscoped->assertOk();
        $this->assertStringContainsString('Comment on blog A', $unscoped->json('data'));
        $this->assertStringContainsString('Comment on blog B', $unscoped->json('data'));
    }

    // G5 — D-FIX-2: destroy de id inexistente → 404 (antes: 200 success)
    public function test_g5_destroy_missing_comment_returns_404(): void
    {
        // Arrange
        $admin = $this->createAdmin();

        // Act + Assert
        $this->actingAs($admin)->deleteJson('/admin/comments/999999')->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    }

    // G6 — DemoMode: el toggle queda bloqueado en demo (espejo Pub7/g11)
    public function test_g6_demo_blocks_comment_status_toggle(): void
    {
        // Arrange — env=demo desactiva runningUnitTests() → CSRF exige token
        app()->detectEnvironment(fn () => 'demo');
        $admin = $this->createAdmin();
        $comment = Comment::factory()->create();

        // Act
        $response = $this->actingAs($admin)
            ->withoutMiddleware(PreventRequestForgery::class)
            ->postJson("/admin/comments/status/{$comment->id}", ['status' => 0]);

        // Assert
        $response->assertStatus(499)
            ->assertJson(['message' => 'This action is disabled in demo mode.']);
        $this->assertSame(1, (int) $comment->fresh()->status);
    }
}

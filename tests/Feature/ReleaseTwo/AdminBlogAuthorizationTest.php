<?php

namespace Tests\Feature\ReleaseTwo;

use App\Domain\Blog\BlogPostStatus;
use App\Domain\Users\Enums\PermissionName;
use App\Domain\Users\Enums\RoleName;
use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminBlogAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_content_manager_can_manage_blog_resources(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole(RoleName::ContentManager->value);

        $this->actingAs($manager);
        $this->get('/admin/blog-posts')->assertOk();
        $this->get('/admin/blog-posts/create')->assertOk();
        $this->get('/admin/blog-categories')->assertOk();
        $this->get('/admin/blog-categories/create')->assertOk();
    }

    public function test_user_without_publish_permission_cannot_publish_with_crafted_form_state(): void
    {
        $editor = $this->editorWithoutPublish();
        $category = BlogCategory::factory()->create();
        $secondTag = BlogCategory::factory()->create();

        Livewire::actingAs($editor)
            ->test(CreateBlogPost::class)
            ->fillForm([
                'title' => 'Новая статья',
                'slug' => 'new-post',
                'tag_ids' => [$category->id, $secondTag->id],
                'content' => [['type' => 'heading', 'data' => ['text' => 'Заголовок', 'level' => 2]]],
                'status' => BlogPostStatus::Published->value,
                'published_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = BlogPost::query()->where('slug', 'new-post')->firstOrFail();
        $this->assertSame(BlogPostStatus::Draft, $post->status);
        $this->assertNull($post->published_at);
        $this->assertEqualsCanonicalizing([$category->id, $secondTag->id], $post->tags()->pluck('blog_categories.id')->all());

        Livewire::actingAs($editor)->test(EditBlogPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['tag_ids' => [$secondTag->id]])->call('save')->assertHasNoFormErrors();
        $this->assertSame([$secondTag->id], $post->fresh()->tags()->pluck('blog_categories.id')->all());
    }

    public function test_user_without_publish_permission_cannot_change_existing_publication_state(): void
    {
        $editor = $this->editorWithoutPublish();
        $post = BlogPost::factory()->create();
        $originalPublishedAt = $post->published_at->toDateTimeString();

        Livewire::actingAs($editor)
            ->test(EditBlogPost::class, ['record' => $post->getRouteKey()])
            ->set('data.title', 'Изменённый заголовок')
            ->set('data.status', BlogPostStatus::Draft->value)
            ->set('data.published_at', null)
            ->call('save')
            ->assertHasNoFormErrors();

        $post->refresh();
        $this->assertSame('Изменённый заголовок', $post->title);
        $this->assertSame(BlogPostStatus::Published, $post->status);
        $this->assertSame($originalPublishedAt, $post->published_at->toDateTimeString());
    }

    public function test_viewer_cannot_create_or_edit_blog_content(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole(RoleName::Viewer->value);
        $post = BlogPost::factory()->create();

        $this->actingAs($viewer)
            ->get('/admin/blog-posts')
            ->assertOk();

        $this->get('/admin/blog-posts/create')->assertForbidden();
        $this->get("/admin/blog-posts/{$post->id}/edit")->assertForbidden();
        $this->get('/admin/blog-categories/create')->assertForbidden();
    }

    private function editorWithoutPublish(): User
    {
        $editor = User::factory()->create();
        $editor->givePermissionTo([
            PermissionName::AdminAccess->value,
            PermissionName::ContentView->value,
            PermissionName::ContentCreate->value,
            PermissionName::ContentUpdate->value,
        ]);

        return $editor;
    }
}

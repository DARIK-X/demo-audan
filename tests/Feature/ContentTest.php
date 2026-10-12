<?php
namespace Tests\Feature;
use App\Models\Article;
use App\Models\Page;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(); }
    private function admin(): User { return User::create(['name' => 'Admin', 'email' => 'content@example.test', 'password' => 'strong-password-123', 'is_admin' => true]); }
    private function articleData(array $changes = []): array { return array_merge(['title' => 'Жаңа материал', 'category' => 'news', 'excerpt' => 'Сипаттама', 'body' => 'Толық мәтін', 'is_published' => true, 'published_at' => now()], $changes); }
    public function test_all_public_pages_render(): void {
        foreach (['/', '/schools', '/resources', '/contacts', '/about', '/admin/login', '/news', '/announcements', '/documents', '/methodology'] as $url) $this->get($url)->assertOk();
        foreach (array_keys(config('portal.pages')) as $slug) $this->get('/pages/'.$slug)->assertOk();
    }
    public function test_public_and_admin_pagination_render_second_page(): void {
        for ($i = 0; $i < 17; $i++) {
            School::create(['name' => 'Мектеп '.$i, 'type' => 'Мектеп-лицей', 'village' => 'Ауыл', 'address' => 'Көше', 'students' => 20, 'is_published' => true]);
            Article::create($this->articleData(['title' => 'Материал '.$i]));
        }
        $this->get('/schools')->assertOk()->assertSee('Келесі');
        $this->get('/schools?page=2')->assertOk()->assertSee('Алдыңғы');
        $this->get('/news?page=2')->assertOk();
        $this->actingAs($this->admin())->get('/admin/schools?page=2')->assertOk();
        $this->get('/admin/articles?page=2')->assertOk();
    }
    public function test_drafts_and_scheduled_materials_are_not_public(): void {
        $draft = Article::create($this->articleData(['is_published' => false, 'title' => 'Жасырын материал']));
        $future = Article::create($this->articleData(['published_at' => now()->addDay(), 'title' => 'Ертеңгі материал']));
        $visible = Article::create($this->articleData());
        $this->get('/news')->assertOk()->assertSee($visible->title)->assertDontSee($draft->title)->assertDontSee($future->title);
        $this->get('/news/'.$draft->id)->assertNotFound();
        $this->get('/news/'.$future->id)->assertNotFound();
        $this->get('/documents/'.$visible->id)->assertNotFound();
    }
    public function test_pdf_download_checks_publication_and_private_disk(): void {
        Storage::fake('local');
        Storage::disk('local')->put('documents/test.pdf', '%PDF-1.4 Test');
        $article = Article::create($this->articleData(['category' => 'documents', 'attachment_path' => 'documents/test.pdf', 'attachment_name' => 'test.pdf']));
        $this->get('/materials/'.$article->id.'/download')->assertOk()->assertDownload('test.pdf');
        $article->update(['is_published' => false]);
        $this->get('/materials/'.$article->id.'/download')->assertNotFound();
    }
    public function test_admin_material_upload_update_and_delete(): void {
        Storage::fake('local'); Storage::fake('public');
        $data = $this->articleData(['category' => 'documents', 'published_at' => now()->format('Y-m-d\TH:i'), 'attachment' => UploadedFile::fake()->createWithContent('plan.pdf', "%PDF-1.4\nexample"), 'image' => UploadedFile::fake()->image('cover.png')]);
        $this->actingAs($this->admin())->post('/admin/articles', $data)->assertRedirect('/admin/articles');
        $article = Article::firstOrFail();
        Storage::disk('local')->assertExists($article->attachment_path);
        Storage::disk('public')->assertExists($article->image_path);
        $oldAttachment = $article->attachment_path; $oldImage = $article->image_path;
        $this->put('/admin/articles/'.$article->id, $this->articleData(['published_at' => null, 'remove_attachment' => 1, 'remove_image' => 1]))->assertRedirect('/admin/articles');
        Storage::disk('local')->assertMissing($oldAttachment); Storage::disk('public')->assertMissing($oldImage);
        $this->delete('/admin/articles/'.$article->id)->assertRedirect('/admin/articles');
        $this->assertDatabaseCount('articles', 0);
    }
    public function test_admin_rejects_executable_attachment_and_invalid_category(): void {
        $this->actingAs($this->admin())->post('/admin/articles', $this->articleData(['published_at' => null, 'category' => 'unknown', 'attachment' => UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;')]))->assertSessionHasErrors(['category', 'attachment']);
        $this->assertDatabaseCount('articles', 0);
    }
    public function test_regular_user_cannot_change_content_settings_or_pages(): void {
        $user = User::create(['name' => 'User', 'email' => 'regular@example.test', 'password' => 'strong-password-123']);
        $page = Page::firstOrFail();
        $this->actingAs($user)->post('/admin/articles', $this->articleData())->assertForbidden();
        $this->put('/admin/settings', ['site_title' => 'Changed'])->assertForbidden();
        $this->put('/admin/pages/'.$page->id, ['title' => 'Changed', 'body' => 'Changed'])->assertForbidden();
    }
    public function test_text_is_escaped_and_settings_are_visible(): void {
        $article = Article::create($this->articleData(['body' => '<script>alert(1)</script>']));
        $this->get('/news/'.$article->id)->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->actingAs($this->admin())->put('/admin/settings', ['site_title' => 'Аудан порталы', 'contact_phone' => '+7 700 000 00 00', 'contact_email' => 'public@example.test'])->assertRedirect();
        $this->get('/contacts')->assertSee('+7 700 000 00 00')->assertSee('public@example.test');
    }
    public function test_page_can_be_hidden_and_seed_does_not_overwrite_edits(): void {
        $page = Page::where('slug', 'teachers')->firstOrFail();
        $this->actingAs($this->admin())->put('/admin/pages/'.$page->id, ['title' => 'Өзгертілген бет', 'body' => 'Жаңа мәтін', 'is_published' => 0])->assertRedirect('/admin/pages');
        $this->seed();
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Өзгертілген бет', 'is_published' => 0]);
        $this->get('/pages/teachers')->assertNotFound();
    }
    public function test_login_logout_and_password_change(): void {
        $admin = $this->admin();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'strong-password-123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertHeader('X-Frame-Options', 'DENY');
        $this->put('/admin/account', ['current_password' => 'wrong', 'password' => 'new-secure-password-456', 'password_confirmation' => 'new-secure-password-456'])->assertSessionHasErrors('current_password');
        $this->put('/admin/account', ['current_password' => 'strong-password-123', 'password' => 'new-secure-password-456', 'password_confirmation' => 'new-secure-password-456'])->assertRedirect();
        $this->assertTrue(Hash::check('new-secure-password-456', $admin->fresh()->password));
        $this->post('/admin/logout')->assertRedirect('/');
        $this->assertGuest();
    }
    public function test_all_admin_forms_render(): void {
        $admin = $this->admin(); $page = Page::firstOrFail();
        $article = Article::create($this->articleData());
        $school = School::create(['name' => 'School', 'type' => 'Мектеп-лицей', 'village' => 'Ауыл', 'address' => 'Көше', 'students' => 20]);
        $this->actingAs($admin);
        foreach (['/admin', '/admin/schools', '/admin/schools/create', '/admin/schools/'.$school->id.'/edit', '/admin/articles', '/admin/articles/create', '/admin/articles/'.$article->id.'/edit', '/admin/pages', '/admin/pages/'.$page->id.'/edit', '/admin/settings', '/admin/account'] as $url) $this->get($url)->assertOk();
    }
    public function test_resource_links_can_be_managed_and_unsafe_urls_are_rejected(): void {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/links')->assertOk();
        $this->get('/admin/links/create')->assertOk();
        $data = ['title' => 'Оқу сервисі', 'description' => 'Сипаттама', 'url' => 'javascript:alert(1)', 'sort_order' => 1, 'is_published' => 1];
        $this->post('/admin/links', $data)->assertSessionHasErrors('url');
        $data['url'] = 'https://example.edu.kz';
        $this->post('/admin/links', $data)->assertRedirect('/admin/links');
        $link = \App\Models\ResourceLink::where('title', $data['title'])->firstOrFail();
        $this->get('/admin/links/'.$link->id.'/edit')->assertOk();
        $this->get('/resources')->assertSee('Оқу сервисі');
        $data['title'] = 'Өңделген сервис'; $data['is_published'] = 0;
        $this->put('/admin/links/'.$link->id, $data)->assertRedirect('/admin/links');
        $this->get('/resources')->assertDontSee('Өңделген сервис');
        $this->seed();
        $this->assertDatabaseHas('resource_links', ['id' => $link->id, 'title' => 'Өңделген сервис']);
        $this->delete('/admin/links/'.$link->id)->assertRedirect('/admin/links');
    }
    public function test_login_is_rate_limited_and_array_email_does_not_cause_server_error(): void {
        $this->post('/admin/login', ['email' => ['invalid'], 'password' => 'invalid'])->assertSessionHasErrors('email');
        for ($i = 0; $i < 4; $i++) $this->post('/admin/login', ['email' => 'invalid@example.test', 'password' => 'invalid'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => 'invalid@example.test', 'password' => 'invalid'])->assertStatus(429);
    }
    public function test_new_upload_is_removed_if_database_save_fails(): void {
        Storage::fake('public');
        $this->actingAs($this->admin())->withoutExceptionHandling();
        $before = School::count();
        School::saving(fn () => throw new \RuntimeException('Simulated database failure'));
        try {
            $this->post('/admin/schools', ['name' => 'Test', 'type' => 'Мектеп-лицей', 'village' => 'Test', 'address' => 'Test', 'students' => 1, 'photo' => UploadedFile::fake()->image('test.png')]);
            $this->fail('Expected simulated failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated database failure', $exception->getMessage());
        } finally { School::flushEventListeners(); }
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('schools', $before);
    }

}

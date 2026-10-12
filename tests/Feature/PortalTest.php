<?php
namespace Tests\Feature;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;
    private function schoolData(): array {
        return ['name' => '№7 мектеп', 'type' => 'Жалпы білім беретін мектеп', 'village' => 'Қызылорда', 'address' => 'Абай көшесі, 1', 'students' => 500, 'website_url' => 'https://example.edu.kz', 'is_published' => 1];
    }
    public function test_only_published_schools_are_public(): void {
        School::create($this->schoolData());
        $draft = School::create(array_merge($this->schoolData(), ['name' => 'Жоба', 'is_published' => false]));
        $this->get('/schools')->assertOk()->assertSee('№7 мектеп')->assertDontSee('Жоба');
        $this->get('/schools/'.$draft->id)->assertNotFound();
    }
    public function test_guest_and_regular_user_cannot_edit_schools(): void {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/schools', $this->schoolData())->assertRedirect('/admin/login');
        $user = User::create(['name' => 'Қолданушы', 'email' => 'user@example.test', 'password' => 'strong-password-123']);
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->post('/admin/schools', $this->schoolData())->assertForbidden();
    }
    public function test_admin_can_create_and_publish_school_and_reject_unsafe_url(): void {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'strong-password-123', 'is_admin' => true]);
        $this->actingAs($admin)->post('/admin/schools', array_merge($this->schoolData(), ['website_url' => 'javascript:alert(1)']))->assertSessionHasErrors('website_url');
        $this->actingAs($admin)->post('/admin/schools', array_merge($this->schoolData(), ['website_url' => ['invalid']]))->assertSessionHasErrors('website_url');
        $this->assertDatabaseCount('schools', 0);
        $this->actingAs($admin)->post('/admin/schools', $this->schoolData())->assertRedirect('/admin/schools');
        $this->assertDatabaseHas('schools', ['name' => '№7 мектеп', 'is_published' => 1]);
        $this->get('/schools')->assertSee('https://example.edu.kz');
    }
    public function test_photo_upload_and_delete(): void {
        Storage::fake('public');
        $admin = User::create(['name' => 'Admin', 'email' => 'admin2@example.test', 'password' => 'strong-password-123', 'is_admin' => true]);
        $this->actingAs($admin)->post('/admin/schools', array_merge($this->schoolData(), ['photo' => UploadedFile::fake()->image('school.jpg')]))->assertRedirect('/admin/schools');
        $school = School::firstOrFail();
        Storage::disk('public')->assertExists($school->photo_path);
        $this->actingAs($admin)->delete('/admin/schools/'.$school->id)->assertRedirect('/admin/schools');
        Storage::disk('public')->assertMissing($school->photo_path);
    }

    private function admin(): User {
        return User::create(['name' => 'Admin', 'email' => 'visibility@example.test', 'password' => 'strong-password-123', 'is_admin' => true]);
    }
    public function test_new_school_defaults_to_published_and_is_first_on_all_lists(): void {
        $this->actingAs($this->admin());
        $this->get('/admin/schools/create')->assertOk()->assertSee('value="1" selected', false);
        for ($i = 0; $i < 20; $i++) School::create(array_merge($this->schoolData(), ['name' => 'Бұрынғы мектеп '.$i]));
        $data = array_merge($this->schoolData(), ['name' => 'Я — жаңа мектеп']);
        unset($data['is_published']);
        $this->post('/admin/schools', $data)->assertRedirect('/admin/schools')->assertSessionHas('school_published', true);
        $school = School::orderByDesc('id')->firstOrFail();
        $this->assertTrue($school->is_published);
        $this->get('/schools')->assertOk()->assertSee('Я — жаңа мектеп')->assertSeeInOrder(['Я — жаңа мектеп', 'Бұрынғы мектеп 19']);
        $this->get('/')->assertOk()->assertSee('Я — жаңа мектеп');
        $this->get('/admin/schools')->assertOk()->assertSee('Я — жаңа мектеп');
    }
    public function test_draft_can_be_published_and_hidden_without_editing_other_fields(): void {
        $school = School::create(array_merge($this->schoolData(), ['name' => 'Бұрын қосылған мектеп', 'is_published' => false]));
        $this->get('/schools')->assertDontSee($school->name);
        $this->actingAs($this->admin())->get('/admin/schools')->assertSee($school->name);
        $this->patch('/admin/schools/'.$school->id.'/publication', ['is_published' => 1])->assertRedirect('/admin/schools');
        $this->get('/schools')->assertSee($school->name);
        $this->get('/schools/'.$school->id)->assertOk();
        $this->patch('/admin/schools/'.$school->id.'/publication', ['is_published' => 0])->assertRedirect('/admin/schools');
        $this->get('/schools')->assertDontSee($school->name);
        $this->get('/schools/'.$school->id)->assertNotFound();
        $this->assertSame($school->name, $school->fresh()->name);
        $this->patch('/admin/schools/'.$school->id.'/publication', ['is_published' => 'invalid'])->assertSessionHasErrors('is_published');
    }
    public function test_edit_without_publication_field_preserves_draft(): void {
        $school = School::create(array_merge($this->schoolData(), ['is_published' => false]));
        $data = array_merge($this->schoolData(), ['name' => 'Өңделген мектеп']);
        unset($data['is_published']);
        $this->actingAs($this->admin())->put('/admin/schools/'.$school->id, $data)->assertRedirect('/admin/schools');
        $this->assertFalse($school->fresh()->is_published);
        $this->get('/schools')->assertDontSee('Өңделген мектеп');
    }
    public function test_publication_endpoint_requires_admin(): void {
        $school = School::create($this->schoolData());
        $this->patch('/admin/schools/'.$school->id.'/publication', ['is_published' => 0])->assertRedirect('/admin/login');
        $user = User::create(['name' => 'User', 'email' => 'ordinary@example.test', 'password' => 'strong-password-123']);
        $this->actingAs($user)->patch('/admin/schools/'.$school->id.'/publication', ['is_published' => 0])->assertForbidden();
        $this->assertTrue($school->fresh()->is_published);
    }
    public function test_kazakh_search_is_case_insensitive_and_wildcards_are_literal(): void {
        $school = School::create(array_merge($this->schoolData(), ['name' => 'ҚАЗАҚ МЕКТЕБІ 100%_!', 'village' => 'ӨРКЕН']));
        School::create(array_merge($this->schoolData(), ['name' => 'Басқа мектеп']));
        $this->get('/schools?q='.urlencode('қазақ'))->assertSee($school->name)->assertDontSee('Басқа мектеп');
        $this->get('/schools?q='.urlencode('өркен'))->assertSee($school->name);
        $this->get('/schools?q='.urlencode('%_!'))->assertSee($school->name)->assertDontSee('Басқа мектеп');
        $this->actingAs($this->admin())->get('/admin/schools?q='.urlencode('қазақ'))->assertSee($school->name)->assertDontSee('Басқа мектеп');
        $school->update(['name' => 'Жаңартылған мектеп']);
        $this->get('/schools?q='.urlencode('жаңартылған'))->assertSee('Жаңартылған мектеп');
        $this->get('/schools?q='.urlencode('қазақ'))->assertDontSee('Жаңартылған мектеп');
    }
    public function test_demo_is_idempotent_preserves_real_school_and_refuses_production(): void {
        $school = School::create($this->schoolData());
        $this->artisan('portal:demo')->assertSuccessful();
        $this->artisan('portal:demo')->assertSuccessful();
        $this->assertDatabaseCount('schools', 4);
        $this->assertDatabaseCount('articles', 4);
        $this->assertSame('№7 мектеп', $school->fresh()->name);
        $this->get('/schools')->assertSee('ДЕМО');
        $this->app->detectEnvironment(fn () => 'production');
        $this->artisan('portal:demo')->assertFailed();
    }

    public function test_requested_school_is_seeded_once_and_stays_first(): void {
        $this->seed();
        $this->seed();
        $school = School::where('website_url', 'https://darik.gdh.kz/demo1/')->sole();
        $this->assertNull($school->students);
        $this->assertSame('Жетісай қаласы, Ынтымақ ауылы', $school->village);
        School::create(array_merge($this->schoolData(), ['name' => 'Кейін қосылған мектеп']));
        $this->get('/schools')->assertSeeInOrder([$school->name, 'Кейін қосылған мектеп'])->assertSee('https://darik.gdh.kz/demo1/');
        $this->get('/')->assertSeeInOrder([$school->name, 'Кейін қосылған мектеп']);
        $this->get('/schools/'.$school->id)->assertSee('Школа №20, школа им. Т. Рыскулова')->assertSee('Көрсетілмеген');
    }

    public function test_seed_reuses_previously_added_draft_school_and_keeps_student_count(): void {
        $school = School::create(array_merge($this->schoolData(), ['name' => 'Т. Рысқұлов атындағы №20 жалпы білім беретін мектеп', 'website_url' => null, 'is_published' => false]));
        $this->seed();
        $this->assertDatabaseCount('schools', 1);
        $this->assertTrue($school->fresh()->is_published);
        $this->assertTrue($school->fresh()->is_featured);
        $this->assertSame(500, $school->fresh()->students);
        $this->assertSame('https://darik.gdh.kz/demo1/', $school->fresh()->website_url);
        $this->get('/schools')->assertSee($school->name);
    }
}

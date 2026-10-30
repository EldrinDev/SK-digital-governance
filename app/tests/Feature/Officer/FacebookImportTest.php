<?php

namespace Tests\Feature\Officer;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\User;
use App\OfficerPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacebookImportTest extends TestCase
{
    use RefreshDatabase;

    private const string PAGE_ID = '1122334455';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.facebook' => ['app_id' => 'app-id', 'app_secret' => 'app-secret', 'graph_version' => 'v26.0']]);
    }

    public function test_chairperson_connects_the_sks_facebook_page(): void
    {
        $chairperson = User::factory()->officer()->create();
        Http::fake([
            'graph.facebook.com/v26.0/oauth/access_token*' => Http::sequence()
                ->push(['access_token' => 'short-lived'])
                ->push(['access_token' => 'long-lived']),
            'graph.facebook.com/v26.0/me/accounts*' => Http::response(['data' => [['id' => self::PAGE_ID, 'name' => 'SK Banicain', 'access_token' => 'page-token']]]),
        ]);

        $redirect = $this->actingAs($chairperson)->get(route('officer.facebook.connect'));
        $redirect->assertRedirectContains('https://www.facebook.com/v26.0/dialog/oauth');
        $this->assertStringContainsString('pages_read_user_content', $redirect->headers->get('Location'));

        $this->actingAs($chairperson)
            ->get(route('officer.facebook.callback', ['code' => 'the-code', 'state' => session('facebook.state')]))
            ->assertRedirect()
            ->assertSessionHas('status');

        $barangay = $chairperson->barangay->fresh();
        $this->assertSame(self::PAGE_ID, $barangay->facebook_page_id);
        $this->assertSame('page-token', $barangay->facebook_page_token);
        $this->assertNotSame('page-token', $barangay->getRawOriginal('facebook_page_token'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'facebook.connected', 'subject_id' => $barangay->id]);
    }

    public function test_apps_with_a_login_configuration_send_its_id_instead_of_permissions(): void
    {
        config(['services.facebook.config_id' => '998877']);

        $location = $this->actingAs(User::factory()->officer()->create())->get(route('officer.facebook.connect'))->headers->get('Location');

        $this->assertStringContainsString('config_id=998877', $location);
        $this->assertStringNotContainsString('scope=', $location);
    }

    public function test_callback_with_a_wrong_state_connects_nothing(): void
    {
        $chairperson = User::factory()->officer()->create();
        Http::fake();

        $this->actingAs($chairperson)->get(route('officer.facebook.connect'));
        $this->actingAs($chairperson)->get(route('officer.facebook.callback', ['code' => 'the-code', 'state' => 'forged']))->assertRedirect();

        $this->assertNull($chairperson->barangay->fresh()->facebook_page_id);
        Http::assertNothingSent();
    }

    public function test_only_the_chairperson_connects_the_page(): void
    {
        $kagawad = User::factory()->officer()->position(OfficerPosition::Kagawad)->create();

        $this->actingAs($kagawad)->get(route('officer.facebook.connect'))->assertForbidden();
        $this->actingAs($kagawad)->get(route('officer.announcements.create'))
            ->assertOk()
            ->assertSeeText('Your SK Chairperson needs to connect');
    }

    public function test_officer_brings_a_post_over_with_its_photo_and_posts_it(): void
    {
        Storage::fake();
        $officer = $this->officerWithConnectedPage();
        Http::fake([
            'graph.facebook.com/v26.0/'.self::PAGE_ID.'_987654321*' => Http::response([
                'id' => self::PAGE_ID.'_987654321',
                'message' => "PAALALA: Katipunan ng Kabataan Assembly\n🗓️ November 7, 2026 (Saturday)\n📍 Covered court\n#LinggoNgKabataan",
                'full_picture' => 'https://scontent.xx.fbcdn.net/v/pubmat.jpg',
                'created_time' => '2026-10-28T10:00:00+0000',
                'permalink_url' => 'https://www.facebook.com/skbanicain/posts/987654321',
            ]),
            'scontent.xx.fbcdn.net/*' => Http::response(UploadedFile::fake()->image('pubmat.jpg')->getContent(), 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $draft = $this->actingAs($officer)
            ->postJson(route('officer.announcements.facebook-import'), ['url' => 'https://www.facebook.com/skbanicain/posts/987654321?__cft__=x'])
            ->assertOk()
            ->assertJson([
                'title' => 'PAALALA: Katipunan ng Kabataan Assembly',
                'category' => 'reminder',
                'source_url' => 'https://www.facebook.com/skbanicain/posts/987654321',
                'originally_posted_at' => '2026-10-28T10:00',
            ])
            ->json();

        $this->assertStringStartsWith('data:image/jpeg;base64,', $draft['image']['preview']);

        $this->actingAs($officer)->post(route('officer.announcements.store'), [
            'category' => $draft['category'],
            'title' => $draft['title'],
            'body' => $draft['body'],
            'source_url' => $draft['source_url'],
            'originally_posted_at' => $draft['originally_posted_at'],
            'imported_image' => $draft['image']['token'],
        ])->assertRedirect(route('officer.announcements.index'));

        $announcement = Announcement::sole();
        $this->assertStringStartsWith("announcements/{$officer->barangay_id}/", $announcement->image_path);
        Storage::assertExists($announcement->image_path);
        $this->assertSame('https://www.facebook.com/skbanicain/posts/987654321', $announcement->source_url);
    }

    public function test_newer_pfbid_links_are_found_among_the_pages_recent_posts(): void
    {
        $officer = $this->officerWithConnectedPage();
        Http::fake([
            'graph.facebook.com/v26.0/'.self::PAGE_ID.'/posts*' => Http::response(['data' => [
                ['id' => self::PAGE_ID.'_1', 'message' => 'Older post', 'created_time' => '2026-10-01T01:00:00+0000', 'permalink_url' => 'https://www.facebook.com/skbanicain/posts/pfbid0aaa'],
                ['id' => self::PAGE_ID.'_2', 'message' => "Maraming salamat sa lahat ng dumalo!\n#SKBanicain", 'created_time' => '2026-10-20T01:00:00+0000', 'permalink_url' => 'https://www.facebook.com/skbanicain/posts/pfbid02XyZ'],
            ]]),
        ]);

        $this->actingAs($officer)
            ->postJson(route('officer.announcements.facebook-import'), ['url' => 'https://m.facebook.com/skbanicain/posts/pfbid02XyZ/?mibextid=abc'])
            ->assertOk()
            ->assertJson(['title' => 'Maraming salamat sa lahat ng dumalo!', 'category' => 'recognition', 'image' => null]);
    }

    public function test_a_post_from_another_page_is_not_brought_over(): void
    {
        $officer = $this->officerWithConnectedPage();
        Http::fake([
            'graph.facebook.com/v26.0/'.self::PAGE_ID.'/posts*' => Http::response(['data' => []]),
            'graph.facebook.com/*' => Http::response(['error' => ['code' => 100, 'message' => 'Unsupported get request.']], 400),
        ]);

        $this->actingAs($officer)
            ->postJson(route('officer.announcements.facebook-import'), ['url' => 'https://www.facebook.com/someoneelse/posts/555'])
            ->assertUnprocessable()
            ->assertJson(['message' => 'That post wasn’t found on SK Banicain. Use a link to one of the Page’s own posts: open the post, then copy the link from its date.']);
    }

    public function test_an_expired_connection_says_to_connect_again(): void
    {
        $officer = $this->officerWithConnectedPage();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['code' => 190, 'message' => 'Error validating access token']], 400)]);

        $this->actingAs($officer)
            ->postJson(route('officer.announcements.facebook-import'), ['url' => 'https://www.facebook.com/skbanicain/posts/987654321'])
            ->assertUnprocessable()
            ->assertJson(['message' => 'The connection to the Facebook Page has expired. Ask your SK Chairperson to connect the Page again.']);
    }

    public function test_links_must_be_facebook_links(): void
    {
        $officer = $this->officerWithConnectedPage();

        $this->actingAs($officer)
            ->postJson(route('officer.announcements.facebook-import'), ['url' => 'https://evil.example/posts/1'])
            ->assertJsonValidationErrors('url');
    }

    public function test_a_made_up_image_token_attaches_nothing(): void
    {
        Storage::fake();
        $officer = $this->officerWithConnectedPage();
        Storage::put('facebook-imports/999/secret.jpg', 'x');

        $this->actingAs($officer)->post(route('officer.announcements.store'), [
            'category' => 'announcement', 'title' => 'Hello', 'body' => 'World', 'imported_image' => 'facebook-imports/999/secret.jpg',
        ]);

        $this->assertNull(Announcement::sole()->image_path);
        Storage::assertExists('facebook-imports/999/secret.jpg');
    }

    public function test_without_a_meta_app_the_import_is_not_offered(): void
    {
        config(['services.facebook.app_id' => null]);
        $officer = $this->officerWithConnectedPage();

        $this->actingAs($officer)->get(route('officer.announcements.create'))->assertOk()->assertDontSeeText('Already posted it on Facebook?');
        $this->actingAs($officer)->postJson(route('officer.announcements.facebook-import'), ['url' => 'https://www.facebook.com/skbanicain/posts/1'])->assertNotFound();
    }

    public function test_chairperson_disconnects_the_page(): void
    {
        $officer = $this->officerWithConnectedPage();

        $this->actingAs($officer)->get(route('officer.announcements.create'))->assertSeeText('Connected to SK Banicain');
        $this->actingAs($officer)->delete(route('officer.facebook.destroy'))->assertRedirect();

        $this->assertFalse($officer->barangay->fresh()->hasFacebookPage());
    }

    private function officerWithConnectedPage(): User
    {
        $barangay = Barangay::factory()->create(['name' => 'Banicain']);
        $barangay->forceFill(['facebook_page_id' => self::PAGE_ID, 'facebook_page_name' => 'SK Banicain', 'facebook_page_token' => 'page-token', 'facebook_connected_at' => now()])->save();

        return User::factory()->officer()->for($barangay)->create();
    }
}

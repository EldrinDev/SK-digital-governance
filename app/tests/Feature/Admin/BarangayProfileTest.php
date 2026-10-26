<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BarangayProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_uploads_a_logo_and_facebook_page_shown_on_public_pages(): void
    {
        Storage::fake();
        $barangay = Barangay::factory()->create(['is_participating' => true, 'name' => 'Gordon Heights']);
        Announcement::factory()->for($barangay)->create(['title' => 'Linggo ng Kabataan']);

        $this->actingAs(User::factory()->administrator()->create())
            ->put(route('admin.barangays.profile.update', $barangay), [
                'logo' => UploadedFile::fake()->image('sk-gordon-heights.png', 512, 512),
                'facebook_url' => 'https://www.facebook.com/skgordonheights',
            ])
            ->assertRedirect(route('admin.barangays.index'));

        $barangay->refresh();
        Storage::assertExists($barangay->logo_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'barangay.updated', 'subject_id' => $barangay->id]);

        $this->get(route('barangays.logo', $barangay))->assertOk();
        $this->get(route('transparency.show', $barangay))
            ->assertSee(route('barangays.logo', $barangay), false)
            ->assertSee('https://www.facebook.com/skgordonheights', false)
            ->assertSeeText('Follow SK Gordon Heights');
        $this->get(route('announcements.index'))->assertSee(route('barangays.logo', $barangay), false);
    }

    public function test_barangay_without_a_logo_falls_back_to_the_national_sk_logo(): void
    {
        $barangay = Barangay::factory()->create();

        $this->assertSame(asset('images/sk-logo.svg'), $barangay->logoUrl());
        $this->get(route('barangays.logo', $barangay))->assertNotFound();
    }

    public function test_facebook_link_must_point_to_facebook(): void
    {
        $barangay = Barangay::factory()->create();
        $admin = User::factory()->administrator()->create();

        foreach (['https://evil.example/facebook.com', 'http://www.facebook.com/sk', 'https://facebook.com.evil.example/sk', 'javascript:alert(1)'] as $url) {
            $this->actingAs($admin)
                ->put(route('admin.barangays.profile.update', $barangay), ['facebook_url' => $url])
                ->assertSessionHasErrors('facebook_url');
        }
    }

    public function test_only_administrators_change_a_barangay_profile(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)->get(route('admin.barangays.edit', $officer->barangay))->assertForbidden();
        $this->actingAs($officer)->put(route('admin.barangays.profile.update', $officer->barangay), ['facebook_url' => 'https://www.facebook.com/x'])->assertForbidden();
    }
}

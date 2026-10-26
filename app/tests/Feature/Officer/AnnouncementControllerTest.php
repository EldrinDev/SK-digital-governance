<?php

namespace Tests\Feature\Officer;

use App\AnnouncementCategory;
use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\User;
use App\Notifications\AnnouncementPosted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_posts_an_announcement_and_youth_are_notified(): void
    {
        Notification::fake();
        $officer = User::factory()->officer()->create();
        $youth = User::factory()->create(['barangay_id' => $officer->barangay_id]);
        $elsewhere = User::factory()->create();

        $this->actingAs($officer)
            ->post(route('officer.announcements.store'), ['category' => 'event', 'title' => 'Summer league sign-ups', 'body' => 'Sign up at the barangay hall.'])
            ->assertRedirect(route('officer.announcements.index'));

        $announcement = Announcement::sole();
        $this->assertSame($officer->barangay_id, $announcement->barangay_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'announcement.posted', 'subject_id' => $announcement->id]);
        Notification::assertSentTo($youth, AnnouncementPosted::class);
        Notification::assertNotSentTo([$officer, $elsewhere], AnnouncementPosted::class);
    }

    public function test_officer_edits_and_takes_down_an_announcement(): void
    {
        $officer = User::factory()->officer()->create();
        $announcement = Announcement::factory()->for($officer->barangay)->create();

        $this->actingAs($officer)->put(route('officer.announcements.update', $announcement), ['category' => 'reminder', 'title' => 'Moved to Saturday', 'body' => 'New date.']);
        $this->assertSame('Moved to Saturday', $announcement->fresh()->title);

        $this->actingAs($officer)->delete(route('officer.announcements.destroy', $announcement));
        $this->assertModelMissing($announcement);
    }

    public function test_announcement_of_another_barangay_is_not_found(): void
    {
        $announcement = Announcement::factory()->create();

        $this->actingAs(User::factory()->officer()->create())
            ->delete(route('officer.announcements.destroy', $announcement))
            ->assertNotFound();

        $this->assertModelExists($announcement);
    }

    public function test_public_sees_announcements_of_participating_barangays_only(): void
    {
        $shown = Announcement::factory()->for(Barangay::factory()->state(['is_participating' => true]))->create(['title' => 'Shown notice']);
        $hidden = Announcement::factory()->for(Barangay::factory()->state(['is_participating' => false]))->create(['title' => 'Hidden notice']);

        $this->get(route('announcements.index'))->assertOk()->assertSeeText('Shown notice')->assertDontSeeText('Hidden notice');
        $this->get(route('announcements.show', $shown))->assertOk();
        $this->get(route('announcements.show', $hidden))->assertNotFound();
        $this->get(route('home'))->assertOk()->assertSeeText('Shown notice');
    }

    public function test_officer_attaches_a_photo_and_pins_the_post(): void
    {
        Storage::fake();
        $officer = User::factory()->officer()->create();
        $officer->barangay->update(['is_participating' => true]);

        $this->actingAs($officer)->post(route('officer.announcements.store'), [
            'category' => 'recognition',
            'title' => 'Salamat sa lahat!',
            'body' => 'Recap ng tournament.',
            'image' => UploadedFile::fake()->image('recap.jpg', 1200, 900),
            'pinned' => '1',
        ]);

        $announcement = Announcement::sole();
        $this->assertTrue($announcement->isPinned());
        $this->assertSame(AnnouncementCategory::Recognition, $announcement->category);
        Storage::assertExists($announcement->image_path);
        $this->get(route('announcements.image', $announcement))->assertOk();

        $this->actingAs($officer)->put(route('officer.announcements.update', $announcement), [
            'category' => 'recognition', 'title' => 'Salamat sa lahat!', 'body' => 'Recap.', 'remove_image' => '1',
        ]);

        Storage::assertMissing($announcement->image_path);
        $this->assertNull($announcement->fresh()->image_path);
        $this->assertFalse($announcement->fresh()->isPinned());
    }

    public function test_pinned_posts_come_first_and_hashtags_are_highlighted_safely(): void
    {
        $barangay = Barangay::factory()->create(['is_participating' => true]);
        Announcement::factory()->for($barangay)->create(['title' => 'Newer post']);
        $pinned = Announcement::factory()->for($barangay)->create([
            'title' => 'Pinned post',
            'body' => 'Tara na! #LinggoNgKabataan <script>alert(1)</script>',
            'created_at' => now()->subWeek(),
        ]);
        $pinned->forceFill(['pinned_at' => now()->subWeek()])->save();

        $this->assertSame('Pinned post', Announcement::query()->pinnedFirst()->first()->title);
        $this->assertStringContainsString('<span class="font-semibold text-emerald">#LinggoNgKabataan</span>', $pinned->captionHtml()->toHtml());
        $this->assertStringNotContainsString('<script>', $pinned->captionHtml()->toHtml());

        $this->get(route('announcements.index'))->assertOk()->assertSeeInOrder(['Pinned post', 'Newer post']);
    }

    public function test_youth_cannot_post_announcements(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('officer.announcements.store'), ['category' => 'announcement', 'title' => 'Nope', 'body' => 'Nope'])
            ->assertForbidden();
    }
}

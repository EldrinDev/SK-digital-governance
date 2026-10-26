<?php

namespace Tests\Feature\Officer;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\User;
use App\Notifications\AnnouncementPosted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
            ->post(route('officer.announcements.store'), ['title' => 'Summer league sign-ups', 'body' => 'Sign up at the barangay hall.'])
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

        $this->actingAs($officer)->put(route('officer.announcements.update', $announcement), ['title' => 'Moved to Saturday', 'body' => 'New date.']);
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

    public function test_youth_cannot_post_announcements(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('officer.announcements.store'), ['title' => 'Nope', 'body' => 'Nope'])
            ->assertForbidden();
    }
}

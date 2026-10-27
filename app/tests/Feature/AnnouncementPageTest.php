<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementPageTest extends TestCase
{
    use RefreshDatabase;

    private function assemblyPost(?Barangay $barangay = null): Announcement
    {
        return Announcement::factory()->for($barangay ?? Barangay::factory()->state(['is_participating' => true]))->create([
            'title' => 'PAALALA: KK Assembly',
            'body' => implode("\n", [
                'Inaanyayahan ang lahat sa ating KK Assembly.',
                '',
                '🗓️ '.now()->addDays(11)->format('F j, Y').' (Saturday)',
                '🕑 2:00 PM',
                '📍 Santa Rita Covered Court',
            ]),
        ]);
    }

    public function test_reads_when_and_where_from_the_caption(): void
    {
        $post = $this->assemblyPost();

        $when = $post->happensAt();
        $this->assertSame(now()->addDays(11)->format('Y-m-d').' 14:00', $when['start']->format('Y-m-d H:i'));
        $this->assertFalse($when['allDay']);
        $this->assertSame('Santa Rita Covered Court', $post->place());

        $this->assertNull(Announcement::factory()->create(['body' => 'No date here.'])->happensAt());
    }

    public function test_page_spells_out_when_and_where_with_actions(): void
    {
        $post = $this->assemblyPost();

        $this->get(route('announcements.show', $post))
            ->assertOk()
            ->assertSeeText('When and where')
            ->assertSeeText('In 11 days')
            ->assertSeeText('Santa Rita Covered Court')
            ->assertSee(route('announcements.calendar', $post), false)
            ->assertSeeText('Open in Maps')
            ->assertSeeText('Share this post');
    }

    public function test_announced_activity_downloads_as_a_calendar_file(): void
    {
        $post = $this->assemblyPost();

        $content = $this->get(route('announcements.calendar', $post))->assertOk()->getContent();

        $this->assertStringContainsString('SUMMARY:PAALALA: KK Assembly', $content);
        $this->assertStringContainsString('LOCATION:Santa Rita Covered Court\\,', $content);
        $this->get(route('announcements.calendar', Announcement::factory()->for(Barangay::factory()->state(['is_participating' => true]))->create(['body' => 'No date.'])))->assertNotFound();
    }

    public function test_youth_of_the_barangay_can_ask_about_the_post(): void
    {
        $post = $this->assemblyPost();
        $youth = User::factory()->create(['barangay_id' => $post->barangay_id]);

        $this->get(route('announcements.show', $post))->assertSeeText('to ask your SK about this post');
        $this->actingAs($youth)->get(route('announcements.show', $post))->assertSeeText('Have a question about this?');
        $this->actingAs(User::factory()->create())->get(route('announcements.show', $post))->assertDontSeeText('Have a question about this?');

        $this->actingAs($youth)
            ->from(route('announcements.show', $post))
            ->post(route('feedback.store'), ['message' => 'Tungkol sa KK Assembly: may snacks po ba?'])
            ->assertRedirect(route('announcements.show', $post));

        $this->assertDatabaseHas('feedback', ['user_id' => $youth->id, 'barangay_id' => $post->barangay_id]);
    }
}

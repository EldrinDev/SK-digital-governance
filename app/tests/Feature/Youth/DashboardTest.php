<?php

namespace Tests\Feature\Youth;

use App\Models\Abyip;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Ppa;
use App\Models\User;
use App\PpaStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_next_registered_event_and_attendance_count(): void
    {
        $youth = User::factory()->create(['name' => 'Yvonne Youth']);
        $later = Event::factory()->for($youth->barangay)->create(['title' => 'Later league game', 'starts_at' => now()->addDays(20)]);
        $sooner = Event::factory()->for($youth->barangay)->create(['title' => 'Sooner clean-up', 'starts_at' => now()->addDays(3)]);
        $past = Event::factory()->for($youth->barangay)->past()->create();
        $later->registrations()->create(['user_id' => $youth->id]);
        $sooner->registrations()->create(['user_id' => $youth->id]);
        $past->registrations()->create(['user_id' => $youth->id])->forceFill(['attended_at' => now()->subDay()])->save();

        $this->actingAs($youth)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Hi, Yvonne')
            ->assertSeeInOrder(['Your next event', 'Sooner clean-up'])
            ->assertViewHas('attendedCount', 1);
    }

    public function test_shows_their_sk_and_how_the_published_fund_is_used(): void
    {
        $abyip = Abyip::factory()->approved()->create(['published_at' => now()]);
        Ppa::factory()->for($abyip)->create(['budget' => 40000, 'amount_spent' => 10000, 'status' => PpaStatus::Completed]);
        User::factory()->officer()->create(['barangay_id' => $abyip->barangay_id, 'name' => 'Carla Chair']);
        $youth = User::factory()->create(['barangay_id' => $abyip->barangay_id]);

        $this->actingAs($youth)
            ->get(route('dashboard'))
            ->assertSeeText('SK Chairperson: Carla Chair')
            ->assertSeeText('₱40,000')
            ->assertSeeText('for 1 project, 1 done')
            ->assertSeeText('You’re all caught up');
    }

    public function test_past_events_show_the_rating_they_gave(): void
    {
        $youth = User::factory()->create();
        $event = Event::factory()->for($youth->barangay)->past()->create(['title' => 'Last month fun run']);
        $event->registrations()->create(['user_id' => $youth->id])->forceFill(['attended_at' => now()->subMonth()])->save();
        $feedback = new Feedback(['event_id' => $event->id, 'rating' => 4, 'message' => 'Great!']);
        $feedback->barangay_id = $youth->barangay_id;
        $youth->feedback()->save($feedback);

        $this->actingAs($youth)
            ->get(route('dashboard'))
            ->assertSeeText('Last month fun run')
            ->assertSeeText('You rated it 4 out of 5')
            ->assertDontSeeText('How did it go?');
    }

    public function test_invites_youth_with_nothing_lined_up_to_browse_events(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSeeText('No events lined up yet')
            ->assertSeeText('Browse events');
    }
}

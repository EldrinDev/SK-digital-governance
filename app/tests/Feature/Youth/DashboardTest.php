<?php

namespace Tests\Feature\Youth;

use App\Models\Event;
use App\Models\User;
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

    public function test_invites_youth_with_nothing_lined_up_to_browse_events(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSeeText('No events lined up yet')
            ->assertSeeText('Browse events');
    }
}

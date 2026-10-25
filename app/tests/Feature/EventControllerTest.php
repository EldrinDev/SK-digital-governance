<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_upcoming_events_of_participating_barangays(): void
    {
        Event::factory()->create(['title' => 'Upcoming sports fest']);
        Event::factory()->past()->create(['title' => 'Last week cleanup']);
        Event::factory()->for(Barangay::factory()->state(['is_participating' => false]))->create(['title' => 'Hidden barangay event']);

        $this->get(route('events.index'))
            ->assertOk()
            ->assertSeeText('Upcoming sports fest')
            ->assertDontSeeText('Last week cleanup')
            ->assertDontSeeText('Hidden barangay event');
    }

    public function test_guest_sees_a_prompt_to_log_in(): void
    {
        $event = Event::factory()->create(['capacity' => 30]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSeeText('30 of 30 places left.')
            ->assertSeeText('Log in to register');
    }

    public function test_event_of_non_participating_barangay_is_not_found(): void
    {
        $event = Event::factory()->for(Barangay::factory()->state(['is_participating' => false]))->create();

        $this->get(route('events.show', $event))->assertNotFound();
    }
}

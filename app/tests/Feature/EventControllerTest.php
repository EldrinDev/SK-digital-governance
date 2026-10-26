<?php

namespace Tests\Feature;

use App\EventKind;
use App\Models\Barangay;
use App\Models\Event;
use App\Models\User;
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

    public function test_filters_by_kind_and_groups_by_month(): void
    {
        Event::factory()->create(['title' => 'Basketball opening', 'kind' => EventKind::Event, 'starts_at' => now()->addDays(3)]);
        Event::factory()->create(['title' => 'ABYIP consultation', 'kind' => EventKind::Consultation, 'starts_at' => now()->addDays(4)]);

        $this->get(route('events.index', ['kind' => 'consultation']))
            ->assertOk()
            ->assertSeeText('ABYIP consultation')
            ->assertDontSeeText('Basketball opening')
            ->assertSeeText(now()->addDays(4)->format('F Y'));
    }

    public function test_youth_sees_which_events_they_registered_for(): void
    {
        $event = Event::factory()->create();
        $youth = User::factory()->create(['barangay_id' => $event->barangay_id]);
        $event->registrations()->create(['user_id' => $youth->id]);

        $this->actingAs($youth)->get(route('events.index'))->assertSeeText('You’re registered');
        $this->actingAs($youth)->get(route('events.show', $event))->assertSeeText('You’re registered')->assertSeeText('Cancel my registration');
    }

    public function test_event_downloads_as_a_calendar_file(): void
    {
        $event = Event::factory()->create(['title' => 'Coastal clean-up, part 2', 'venue' => 'Seawall']);

        $response = $this->get(route('events.calendar', $event))->assertOk();

        $this->assertStringStartsWith('text/calendar', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('SUMMARY:Coastal clean-up\\, part 2', $response->getContent());
        $this->assertStringContainsString('DTSTART:'.$event->starts_at->copy()->utc()->format('Ymd\\THis\\Z'), $response->getContent());
    }

    public function test_event_of_non_participating_barangay_is_not_found(): void
    {
        $event = Event::factory()->for(Barangay::factory()->state(['is_participating' => false]))->create();

        $this->get(route('events.show', $event))->assertNotFound();
    }
}

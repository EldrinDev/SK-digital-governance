<?php

namespace Tests\Feature\Officer;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_posts_an_event_in_own_barangay(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)->post(route('officer.events.store'), [
            'kind' => 'consultation',
            'title' => 'Youth budget consultation',
            'starts_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'venue' => 'Barangay hall',
            'capacity' => 40,
        ])->assertRedirect();

        $event = Event::sole();
        $this->assertSame($officer->barangay_id, $event->barangay_id);
        $this->assertSame(40, $event->capacity);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.created']);
    }

    public function test_event_in_the_past_is_rejected(): void
    {
        $this->actingAs(User::factory()->officer()->create())->post(route('officer.events.store'), [
            'kind' => 'event',
            'title' => 'Too late',
            'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'venue' => 'Hall',
        ])->assertSessionHasErrors(['starts_at' => 'The event must start in the future.']);
    }

    public function test_check_in_opens_on_the_day_of_the_event(): void
    {
        $registration = EventRegistration::factory()->create();
        $event = $registration->event;
        $officer = User::factory()->officer()->create(['barangay_id' => $event->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => '1'])
            ->assertSessionHasErrors(['attendance' => 'Check-in opens on the day of the event.']);

        $event->update(['starts_at' => now()->subHour()]);

        $this->actingAs($officer)->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => '1']);
        $this->assertTrue($registration->fresh()->attended());
    }

    public function test_registration_of_another_event_is_not_found(): void
    {
        $event = Event::factory()->past()->create();
        $other = EventRegistration::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $event->barangay_id]);

        $this->actingAs($officer)
            ->patch(route('officer.events.registrations.update', [$event, $other]), ['attended' => '1'])
            ->assertNotFound();
    }

    public function test_event_of_another_barangay_is_not_found(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->get(route('officer.events.show', Event::factory()->create()))
            ->assertNotFound();
    }
}

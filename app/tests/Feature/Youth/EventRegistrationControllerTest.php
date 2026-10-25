<?php

namespace Tests\Feature\Youth;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Notifications\EventRegistrationConfirmed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventRegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    private Event $event;

    private User $youth;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->event = Event::factory()->create(['title' => 'Basketball league opening']);
        $this->youth = User::factory()->create(['barangay_id' => $this->event->barangay_id]);
    }

    public function test_youth_registers_and_gets_a_confirmation(): void
    {
        $this->actingAs($this->youth)
            ->post(route('events.registration.store', $this->event))
            ->assertSessionHas('status', 'You’re registered. We emailed you the details.');

        $this->assertDatabaseHas('event_registrations', ['event_id' => $this->event->id, 'user_id' => $this->youth->id]);
        Notification::assertSentTo($this->youth, EventRegistrationConfirmed::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.registered', 'user_id' => $this->youth->id]);
    }

    public function test_registering_twice_is_rejected(): void
    {
        $this->actingAs($this->youth)->post(route('events.registration.store', $this->event));

        $this->actingAs($this->youth)
            ->post(route('events.registration.store', $this->event))
            ->assertSessionHasErrors(['registration' => 'You’re already registered for this event.']);

        $this->assertDatabaseCount('event_registrations', 1);
    }

    public function test_full_event_is_rejected(): void
    {
        $this->event->update(['capacity' => 1]);
        EventRegistration::factory()->for($this->event)->create();

        $this->actingAs($this->youth)
            ->post(route('events.registration.store', $this->event))
            ->assertSessionHasErrors(['registration' => 'Sorry, every place is taken.']);
    }

    public function test_youth_of_another_barangay_is_rejected(): void
    {
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->post(route('events.registration.store', $this->event))
            ->assertSessionHasErrors('registration');

        $this->assertDatabaseCount('event_registrations', 0);
        Notification::assertNothingSent();
    }

    public function test_started_event_is_closed(): void
    {
        $past = Event::factory()->past()->create(['barangay_id' => $this->youth->barangay_id]);

        $this->actingAs($this->youth)
            ->post(route('events.registration.store', $past))
            ->assertSessionHasErrors(['registration' => 'Registration is closed because the event has started.']);
    }

    public function test_officers_cannot_register(): void
    {
        $officer = User::factory()->officer()->create(['barangay_id' => $this->event->barangay_id]);

        $this->actingAs($officer)->post(route('events.registration.store', $this->event))->assertForbidden();
    }

    public function test_youth_cancels_before_the_event(): void
    {
        EventRegistration::factory()->for($this->event)->create(['user_id' => $this->youth->id]);

        $this->actingAs($this->youth)->delete(route('events.registration.destroy', $this->event));

        $this->assertDatabaseCount('event_registrations', 0);
    }
}

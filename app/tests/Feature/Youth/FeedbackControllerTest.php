<?php

namespace Tests\Feature\Youth;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_sends_general_feedback_to_own_barangay(): void
    {
        $youth = User::factory()->create();

        $this->actingAs($youth)
            ->post(route('feedback.store'), ['message' => 'Please hold a coding workshop.'])
            ->assertSessionHas('status', 'Your feedback was sent to your SK.');

        $this->assertDatabaseHas('feedback', [
            'user_id' => $youth->id,
            'barangay_id' => $youth->barangay_id,
            'event_id' => null,
            'message' => 'Please hold a coding workshop.',
        ]);
    }

    public function test_youth_who_attended_evaluates_once(): void
    {
        $registration = EventRegistration::factory()->attended()->for(Event::factory()->past())->create();
        $youth = $registration->user;

        $this->actingAs($youth)
            ->post(route('feedback.store'), ['event_id' => $registration->event_id, 'rating' => 4, 'message' => 'Well organized.'])
            ->assertSessionHas('status', 'Thanks for your evaluation.');

        $this->actingAs($youth)
            ->post(route('feedback.store'), ['event_id' => $registration->event_id, 'rating' => 5, 'message' => 'Again'])
            ->assertSessionHasErrors(['event_id' => 'You already evaluated this event.']);

        $this->assertDatabaseCount('feedback', 1);
    }

    public function test_cannot_evaluate_an_event_not_attended(): void
    {
        $registration = EventRegistration::factory()->for(Event::factory()->past())->create();

        $this->actingAs($registration->user)
            ->post(route('feedback.store'), ['event_id' => $registration->event_id, 'rating' => 5, 'message' => 'Great'])
            ->assertSessionHasErrors(['event_id' => 'You can only evaluate events you attended.']);
    }

    public function test_evaluation_needs_a_rating(): void
    {
        $registration = EventRegistration::factory()->attended()->for(Event::factory()->past())->create();

        $this->actingAs($registration->user)
            ->post(route('feedback.store'), ['event_id' => $registration->event_id, 'message' => 'No stars'])
            ->assertSessionHasErrors(['rating' => 'Rate the event from 1 to 5.']);
    }

    public function test_dashboard_asks_to_evaluate_attended_events(): void
    {
        $registration = EventRegistration::factory()->attended()->for(Event::factory()->past()->state(['title' => 'Coastal clean-up']))->create();

        $this->actingAs($registration->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('How did it go?')
            ->assertSeeText('Coastal clean-up');
    }
}

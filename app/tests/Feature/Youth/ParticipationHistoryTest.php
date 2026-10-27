<?php

namespace Tests\Feature\Youth;

use App\CheckInMethod;
use App\Models\Event;
use App\Models\Feedback;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_check_in_records_the_method_and_the_officer(): void
    {
        $youth = User::factory()->create(['name' => 'Carlo Cruz']);
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id, 'name' => 'Oscar Officer']);
        $event = Event::factory()->for($youth->barangay)->create(['starts_at' => now()->addHour()]);
        $registration = $event->registrations()->create(['user_id' => $youth->id]);

        $this->actingAs($officer)->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => 1, 'method' => 'qr']);

        $registration->refresh();
        $this->assertSame(CheckInMethod::Qr, $registration->check_in_method);
        $this->assertTrue($registration->checkedInBy->is($officer));
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.checked_in', 'properties->method' => 'QR scan']);

        $this->actingAs($officer)
            ->get(route('officer.events.show', $event))
            ->assertSeeText('1 by QR scan')
            ->assertSeeText('with their QR, by Oscar Officer');

        $csv = $this->actingAs($officer)->get(route('officer.events.export', $event))->streamedContent();
        $this->assertStringContainsString('"Checked in via","Checked in by"', $csv);
        $this->assertStringContainsString('"QR scan","Oscar Officer"', $csv);
    }

    public function test_manual_check_in_is_the_default_and_undo_clears_the_record(): void
    {
        $youth = User::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id]);
        $event = Event::factory()->for($youth->barangay)->create(['starts_at' => now()->addHour()]);
        $registration = $event->registrations()->create(['user_id' => $youth->id]);

        $this->actingAs($officer)->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => 1]);
        $this->assertSame(CheckInMethod::Manual, $registration->fresh()->check_in_method);

        $this->actingAs($officer)->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => 0]);
        $registration->refresh();
        $this->assertNull($registration->check_in_method);
        $this->assertNull($registration->checked_in_by);
    }

    public function test_activity_page_shows_attended_missed_and_upcoming_events_with_surveys_and_messages(): void
    {
        $youth = User::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id]);

        $attended = Event::factory()->for($youth->barangay)->past()->create(['title' => 'Fun run']);
        $attended->registrations()->create(['user_id' => $youth->id])->forceFill(['attended_at' => now()->subDays(3), 'check_in_method' => CheckInMethod::Qr, 'checked_in_by' => $officer->id])->save();
        $missed = Event::factory()->for($youth->barangay)->past()->create(['title' => 'Missed seminar']);
        $missed->registrations()->create(['user_id' => $youth->id]);
        $upcoming = Event::factory()->for($youth->barangay)->create(['title' => 'Next week league']);
        $upcoming->registrations()->create(['user_id' => $youth->id]);

        $survey = Survey::factory()->opened()->for($youth->barangay)->create(['title' => 'Programs survey']);
        $survey->responses()->create(['user_id' => $youth->id]);
        $message = new Feedback(['message' => 'Study hub please']);
        $message->barangay_id = $youth->barangay_id;
        $youth->feedback()->save($message);

        $this->actingAs($youth)
            ->get(route('account.activity'))
            ->assertOk()
            ->assertSeeText('Fun run')
            ->assertSeeText('by qr scan')
            ->assertSeeText('Missed seminar')
            ->assertSeeText('You registered but weren’t checked in.')
            ->assertSeeText('Next week league')
            ->assertSeeText('Programs survey')
            ->assertSeeText('Study hub please')
            ->assertSeeText('50%')
            ->assertSeeText('1 checked in by QR scan');

        $this->actingAs($youth)
            ->get(route('account.activity', ['type' => 'surveys']))
            ->assertSeeText('Programs survey')
            ->assertDontSeeText('Fun run');
    }

    public function test_only_youth_have_an_activity_page(): void
    {
        $this->actingAs(User::factory()->officer()->create())->get(route('account.activity'))->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Federation;

use App\Models\Barangay;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipationAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_check_ins_count_attendance_per_month(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15));
        $event = Event::factory()->create();

        foreach ([now()->subMonth(), now()->subMonth(), now(), null] as $attendedAt) {
            $registration = $event->registrations()->create(['user_id' => User::factory()->create(['barangay_id' => $event->barangay_id])->id]);
            $registration->forceFill(['attended_at' => $attendedAt])->save();
        }

        $months = EventRegistration::monthlyCheckIns([$event->barangay_id]);

        $this->assertCount(6, $months);
        $this->assertSame(['label' => 'Sep', 'value' => 2], $months[4]);
        $this->assertSame(['label' => 'Oct', 'value' => 1], $months[5]);
        $this->assertSame(0, EventRegistration::monthlyCheckIns([Barangay::factory()->create()->id])[5]['value']);
    }

    public function test_federation_dashboard_shows_participants_and_charts(): void
    {
        $barangay = Barangay::factory()->create(['is_participating' => true, 'name' => 'Santa Rita']);
        $event = Event::factory()->for($barangay)->create();
        $youth = User::factory()->create(['barangay_id' => $barangay->id]);
        $event->registrations()->create(['user_id' => $youth->id])->forceFill(['attended_at' => now()])->save();

        $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.dashboard'))
            ->assertOk()
            ->assertSeeText('Youth participants in '.now()->year)
            ->assertSeeText('PPA completion')
            ->assertSeeText('Youth check-ins')
            ->assertSeeText('Latest monitoring remarks');
    }
}

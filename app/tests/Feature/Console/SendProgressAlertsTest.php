<?php

namespace Tests\Feature\Console;

use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\Milestone;
use App\Models\Ppa;
use App\Models\User;
use App\Notifications\ProgressAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendProgressAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_officers_get_a_digest_of_what_needs_attention(): void
    {
        Notification::fake();
        $barangay = Barangay::factory()->create(['is_participating' => true]);
        $abyip = Abyip::factory()->approved()->for($barangay)->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $barangay->id]);
        $youth = User::factory()->create(['barangay_id' => $barangay->id]);

        Ppa::factory()->delayed()->for($abyip)->create();
        $ongoing = Ppa::factory()->for($abyip)->create();
        Milestone::factory()->for($ongoing)->create(['due_on' => today()->subDays(2)]);
        Milestone::factory()->for($ongoing)->create(['due_on' => today()->addDay()]);
        Milestone::factory()->completed()->for($ongoing)->create(['due_on' => today()->subDays(5)]);

        $this->artisan('app:send-progress-alerts')->assertSuccessful();

        Notification::assertSentTo($officer, ProgressAlert::class, function (ProgressAlert $alert, array $channels, object $notifiable): bool {
            return $alert->delayedPpas === 1
                && $alert->overdueMilestones === 1
                && $alert->milestonesDueSoon === 1
                && str_contains($alert->toArray($notifiable)['body'], '1 milestone is overdue.');
        });
        Notification::assertNotSentTo($youth, ProgressAlert::class);
    }

    public function test_nothing_is_sent_when_all_is_on_track_or_the_plan_is_unapproved(): void
    {
        Notification::fake();
        $barangay = Barangay::factory()->create(['is_participating' => true]);
        User::factory()->officer()->create(['barangay_id' => $barangay->id]);
        $draftPpa = Ppa::factory()->for(Abyip::factory()->for($barangay))->create();
        Milestone::factory()->for($draftPpa)->create(['due_on' => today()->subDays(2)]);

        $this->artisan('app:send-progress-alerts')->assertSuccessful();

        Notification::assertNothingSent();
    }
}

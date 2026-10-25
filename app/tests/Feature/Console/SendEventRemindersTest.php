<?php

namespace Tests\Feature\Console;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Notifications\EventReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendEventRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminds_registrants_of_tomorrows_events_only_once(): void
    {
        Notification::fake();
        $tomorrow = EventRegistration::factory()->for(Event::factory()->state(['starts_at' => now()->addDay()->setTime(9, 0)]))->create();
        $nextWeek = EventRegistration::factory()->create();

        $this->artisan('app:send-event-reminders')->assertSuccessful();
        $this->artisan('app:send-event-reminders')->assertSuccessful();

        Notification::assertSentToTimes($tomorrow->user, EventReminder::class, 1);
        Notification::assertNotSentTo($nextWeek->user, EventReminder::class);
    }
}

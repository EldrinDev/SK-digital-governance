<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-event-reminders')]
#[Description('Email registered youth about events happening tomorrow')]
class SendEventReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $events = Event::query()
            ->whereBetween('starts_at', [today()->addDay()->startOfDay(), today()->addDay()->endOfDay()])
            ->whereNull('reminders_sent_at')
            ->with('registrations.user')
            ->get();

        foreach ($events as $event) {
            foreach ($event->registrations as $registration) {
                $registration->user->notify(new EventReminder($event));
            }

            $event->forceFill(['reminders_sent_at' => now()])->save();
        }

        $this->info("Sent reminders for {$events->count()} event(s).");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Barangay;
use App\Models\Milestone;
use App\Notifications\ProgressAlert;
use App\PlanStatus;
use App\PpaStatus;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('app:send-progress-alerts {--days=3 : How many days ahead counts as "due soon"}')]
#[Description('Send each barangay’s officers a digest of delayed PPAs and overdue or upcoming milestones')]
class SendProgressAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $sent = 0;

        foreach (Barangay::participating()->get() as $barangay) {
            $openMilestones = Milestone::query()
                ->whereNull('completed_at')
                ->whereHas('ppa', fn ($ppa) => $ppa->where('barangay_id', $barangay->id)
                    ->whereIn('status', [PpaStatus::Planned, PpaStatus::Ongoing])
                    ->whereHas('abyip', fn ($abyip) => $abyip->where('status', PlanStatus::Approved)));

            $overdue = (clone $openMilestones)->whereDate('due_on', '<', today())->count();
            $dueSoon = (clone $openMilestones)->whereBetween('due_on', [today()->toDateString(), today()->addDays($days)->toDateString()])->count();
            $delayed = $barangay->ppas()->delayed()->count();

            if ($overdue + $dueSoon + $delayed === 0) {
                continue;
            }

            $officers = $barangay->activeOfficers();

            if ($officers->isEmpty()) {
                continue;
            }

            Notification::send($officers, new ProgressAlert($delayed, $overdue, $dueSoon, $days));
            $sent++;
        }

        $this->info("Sent progress alerts to {$sent} barangay(s).");

        return self::SUCCESS;
    }
}

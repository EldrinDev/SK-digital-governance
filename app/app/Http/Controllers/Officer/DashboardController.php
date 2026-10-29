<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\EventRegistration;
use App\Models\Milestone;
use App\Models\User;
use App\PlanStatus;
use App\PpaStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the barangay workspace summary: what needs doing next, PPA counts, budget,
     * open PPAs and what's coming up.
     */
    public function __invoke(Request $request): View
    {
        $barangay = $request->user()->barangay;
        $year = (int) now()->format('Y');

        $currentAbyip = $barangay->abyips()->withCount('ppas')->where('year', $year)->first();
        $ppas = $barangay->ppas();
        $completedPpas = (clone $ppas)->where('status', PpaStatus::Completed)->count();
        $completedWithEvidence = (clone $ppas)->where('status', PpaStatus::Completed)->has('evidence')->count();

        return view('officer.dashboard', [
            'barangay' => $barangay,
            'nextSteps' => $this->nextSteps($request->user(), $barangay, $year, $currentAbyip, $completedPpas - $completedWithEvidence),
            'year' => $year,
            'yearElapsedPercent' => (int) round(now()->dayOfYear / now()->daysInYear * 100),
            'currentAbyip' => $currentAbyip,
            'currentPpaCount' => $currentAbyip?->ppas()->where('status', '!=', PpaStatus::Cancelled)->count() ?? 0,
            'currentPpaCompleted' => $currentAbyip?->ppas()->where('status', PpaStatus::Completed)->count() ?? 0,
            'totalPpas' => (clone $ppas)->count(),
            'completedPpas' => $completedPpas,
            'completedWithEvidence' => $completedWithEvidence,
            'delayedPpas' => (clone $ppas)->delayed()->count(),
            'plannedBudget' => $plannedBudget = (float) ($currentAbyip?->ppas()->where('status', '!=', PpaStatus::Cancelled)->sum('budget') ?? 0),
            'spentBudget' => $spentBudget = (float) ($currentAbyip?->ppas()->sum('amount_spent') ?? 0),
            'budgetUsedPercent' => $plannedBudget > 0 ? (int) min(100, round($spentBudget / $plannedBudget * 100)) : 0,
            'activePpas' => (clone $ppas)
                ->whereIn('status', [PpaStatus::Planned, PpaStatus::Ongoing])
                ->with(['milestones', 'abyip:id,status,year'])
                ->orderBy('ends_on')
                ->limit(5)
                ->get(),
            'agenda' => $this->agenda($barangay),
            'monthlyCheckIns' => EventRegistration::monthlyCheckIns([$barangay->id]),
            'remarks' => $barangay->remarks()->with('author:id,name')->limit(3)->get(),
        ]);
    }

    /**
     * Events, council sessions and milestones still ahead (and milestones already late),
     * in date order, so the week's work reads as one list.
     *
     * @return Collection<int, array{date: Carbon, title: string, detail: string, href: string, overdue: bool}>
     */
    private function agenda(Barangay $barangay): Collection
    {
        $events = $barangay->events()->upcoming()->withCount('registrations')->orderBy('starts_at')->limit(6)->get()
            ->map(fn ($event): array => [
                'date' => $event->starts_at,
                'title' => $event->title,
                'detail' => $event->kind->label().' at '.$event->starts_at->format('g:i A').', '
                    .$event->registrations_count.($event->capacity ? ' of '.$event->capacity : '').' registered',
                'href' => route('officer.events.show', $event),
                'overdue' => false,
            ]);

        $meetings = $barangay->meetings()->whereDate('held_on', '>=', today())->orderBy('held_on')->limit(6)->get()
            ->map(fn ($meeting): array => [
                'date' => $meeting->held_on,
                'title' => $meeting->title,
                'detail' => $meeting->kind->label().($meeting->venue ? ', '.$meeting->venue : ''),
                'href' => route('officer.meetings.show', $meeting),
                'overdue' => false,
            ]);

        $milestones = Milestone::query()
            ->whereHas('ppa', fn ($query) => $query->where('barangay_id', $barangay->id)->whereIn('status', [PpaStatus::Planned, PpaStatus::Ongoing]))
            ->whereNull('completed_at')
            ->with('ppa:id,title')
            ->orderBy('due_on')
            ->limit(6)
            ->get()
            ->map(fn ($milestone): array => [
                'date' => $milestone->due_on,
                'title' => $milestone->title,
                'detail' => 'Milestone of '.$milestone->ppa->title,
                'href' => route('officer.ppas.show', $milestone->ppa_id),
                'overdue' => $milestone->isOverdue(),
            ]);

        return $events->toBase()->merge($meetings)->merge($milestones)
            ->sortBy(fn (array $item): int => $item['date']->getTimestamp())
            ->take(7)
            ->values();
    }

    /**
     * Work waiting for the officers, most pressing first: late work, then the next step
     * in this year's plan, then loose ends such as missing evidence and unanswered messages.
     *
     * @return list<array{text: string, detail: string, action: string, href: string, urgent: bool}>
     */
    private function nextSteps(User $user, Barangay $barangay, int $year, ?Abyip $currentAbyip, int $completedWithoutEvidence): array
    {
        $steps = [];

        $delayed = $barangay->ppas()->delayed()->orderBy('ends_on')->pluck('title');

        if ($delayed->isNotEmpty()) {
            $steps[] = [
                'text' => trans_choice(':count PPA is past its end date and still open|:count PPAs are past their end date and still open', $delayed->count()),
                'detail' => $delayed->take(2)->join(', ').($delayed->count() > 2 ? ', and '.($delayed->count() - 2).' more' : '').'. Mark each one completed, or cancel it.',
                'action' => 'Review delayed PPAs',
                'href' => route('officer.ppas.index', ['status' => 'delayed']),
                'urgent' => true,
            ];
        }

        $overdueMilestones = Milestone::query()
            ->whereHas('ppa', fn ($query) => $query->where('barangay_id', $barangay->id)->whereIn('status', [PpaStatus::Planned, PpaStatus::Ongoing]))
            ->whereNull('completed_at')
            ->whereDate('due_on', '<', today())
            ->count();

        if ($overdueMilestones > 0) {
            $steps[] = [
                'text' => trans_choice(':count milestone is overdue|:count milestones are overdue', $overdueMilestones),
                'detail' => 'Tick off the ones that are done, so the progress bars stay true.',
                'action' => 'See PPAs',
                'href' => route('officer.ppas.index'),
                'urgent' => true,
            ];
        }

        if ($currentAbyip === null) {
            $cbydp = $barangay->cbydps()->where('status', PlanStatus::Approved)->get()->first(fn ($cbydp) => $cbydp->covers($year));

            $steps[] = $cbydp
                ? [
                    'text' => "There’s no ABYIP for {$year} yet",
                    'detail' => "Start it from CBYDP {$cbydp->period()}, then add this year’s PPAs.",
                    'action' => "Start ABYIP {$year}",
                    'href' => route('officer.abyips.create', ['cbydp' => $cbydp->id]),
                    'urgent' => false,
                ]
                : [
                    'text' => "No approved CBYDP covers {$year}",
                    'detail' => 'An ABYIP comes from an approved three-year plan. Set one up and record the council’s approval.',
                    'action' => 'Go to plans',
                    'href' => route('officer.plans.index'),
                    'urgent' => false,
                ];
        } elseif (! $currentAbyip->isApproved()) {
            $steps[] = $currentAbyip->ppas_count === 0
                ? [
                    'text' => "ABYIP {$year} has no PPAs yet",
                    'detail' => 'List the programs, projects and activities for the year, each with a budget and schedule.',
                    'action' => 'Add PPAs',
                    'href' => route('officer.abyips.show', $currentAbyip),
                    'urgent' => false,
                ]
                : [
                    'text' => "ABYIP {$year} is waiting for the council’s approval",
                    'detail' => 'Once the council approves it, upload the signed resolution to start implementation.',
                    'action' => 'Record approval',
                    'href' => route('officer.abyips.show', $currentAbyip),
                    'urgent' => false,
                ];
        } elseif (! $currentAbyip->isPublished() && $user->can('publish', $currentAbyip)) {
            $steps[] = [
                'text' => "ABYIP {$year} is approved but not on the public board",
                'detail' => 'Publishing shows its PPAs, budgets and progress to the youth and the public.',
                'action' => 'Publish it',
                'href' => route('officer.abyips.show', $currentAbyip),
                'urgent' => false,
            ];
        }

        if ($completedWithoutEvidence > 0) {
            $steps[] = [
                'text' => trans_choice(':count completed PPA has no evidence|:count completed PPAs have no evidence', $completedWithoutEvidence),
                'detail' => 'Upload photos, attendance sheets or receipts. The accomplishment report needs them.',
                'action' => 'Upload evidence',
                'href' => route('officer.ppas.index', ['status' => 'completed']),
                'urgent' => false,
            ];
        }

        $waitingMessages = $barangay->feedback()->whereNull('responded_at')->count();

        if ($waitingMessages > 0) {
            $steps[] = [
                'text' => trans_choice(':count message from the youth is waiting for a reply|:count messages from the youth are waiting for a reply', $waitingMessages),
                'detail' => 'They see your reply on their dashboard.',
                'action' => 'Reply',
                'href' => route('officer.feedback.index'),
                'urgent' => false,
            ];
        }

        return $steps;
    }
}

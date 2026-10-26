<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Ppa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    use StreamsCsv;

    /**
     * Download every PPA in the barangay as a CSV file.
     */
    public function ppas(Request $request): StreamedResponse
    {
        $barangay = $request->user()->barangay;

        $ppas = $barangay->ppas()
            ->with(['abyip:id,year,status', 'priority:id,center,title', 'milestones'])
            ->withCount('evidence')
            ->orderBy('starts_on')
            ->lazy();

        return $this->csv(str($barangay->name)->slug().'-ppas-'.now()->format('Y-m-d').'.csv',
            ['ABYIP', 'PPA', 'Center of participation', 'Priority', 'Target', 'Budget', 'Starts', 'Ends', 'Status', 'Delayed', 'Milestones done', 'Evidence files'],
            $ppas->map(fn (Ppa $ppa): array => [
                $ppa->abyip->year,
                $ppa->title,
                $ppa->priority->center->label(),
                $ppa->priority->title,
                $ppa->target,
                $ppa->budget,
                $ppa->starts_on->toDateString(),
                $ppa->ends_on->toDateString(),
                $ppa->status->label(),
                $ppa->isDelayed() ? 'Yes' : 'No',
                $ppa->milestones->whereNotNull('completed_at')->count().' of '.$ppa->milestones->count(),
                $ppa->evidence_count,
            ]),
        );
    }

    /**
     * Download an event's registrants and check-ins as a CSV file.
     */
    public function eventRegistrations(Event $event): StreamedResponse
    {
        Gate::authorize('update', $event);

        return $this->csv(str($event->title)->slug().'-registrants.csv',
            ['Name', 'Email', 'Registered', 'Checked in'],
            $event->registrations()->with('user:id,name,email')->oldest()->lazy()->map(fn (EventRegistration $registration): array => [
                $registration->user->name,
                $registration->user->email,
                $registration->created_at->toDateTimeString(),
                $registration->attended_at?->toDateTimeString() ?? 'No',
            ]),
        );
    }
}

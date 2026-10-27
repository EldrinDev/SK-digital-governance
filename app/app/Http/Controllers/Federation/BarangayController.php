<?php

namespace App\Http\Controllers\Federation;

use App\DocumentExporter;
use App\ExportDocument;
use App\ExportFormat;
use App\Http\Controllers\Controller;
use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\Cbydp;
use App\Models\Meeting;
use App\Models\MonitoringRemark;
use App\Models\Ppa;
use App\OfficerPosition;
use App\PpaStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BarangayController extends Controller
{
    /**
     * Read-only view of one barangay: plans, PPAs, sessions and monitoring remarks.
     */
    public function show(Barangay $barangay): View
    {
        abort_unless($barangay->is_participating, 404);

        return view('federation.barangays.show', [
            'barangay' => $barangay,
            'cbydps' => $barangay->cbydps()->withCount('priorities')->orderByDesc('start_year')->get(),
            'abyips' => $barangay->abyips()
                ->with(['ppas' => fn ($query) => $query->with(['milestones', 'priority'])->withCount('evidence')->orderBy('starts_on')])
                ->orderByDesc('year')
                ->get(),
            'meetings' => $barangay->meetings()->with('attendees')->withCount('resolutions')->orderByDesc('held_on')->limit(5)->get(),
            'remarks' => $barangay->remarks()->with('author:id,name')->get(),
        ]);
    }

    /**
     * Download this barangay's plans, PPAs, sessions and remarks as Excel, PDF, Word or CSV.
     */
    public function export(Request $request, Barangay $barangay): Response
    {
        abort_unless($barangay->is_participating, 404);

        $format = ExportFormat::from($request->validate([
            'format' => ['nullable', Rule::enum(ExportFormat::class)],
        ])['format'] ?? ExportFormat::Excel->value);

        $cbydps = $barangay->cbydps()->withCount('priorities')->orderByDesc('start_year')->get();
        $abyips = $barangay->abyips()
            ->with(['ppas' => fn ($query) => $query->with(['milestones', 'priority'])->withCount('evidence')->orderBy('starts_on')])
            ->orderByDesc('year')
            ->get();

        $document = ExportDocument::make("SK {$barangay->name}", 'Plans, programs, sessions and monitoring remarks.')
            ->detail('Barangay', $barangay->name)
            ->detail('SK Chairperson', $barangay->activeOfficers()->first(fn ($officer) => $officer->holdsPosition(OfficerPosition::Chairperson))?->name)
            ->table('Plans', ['Plan' => 'text', 'Covers' => 'text', 'Status' => 'text', 'Resolution' => 'text', 'Size' => 'text'], [
                ...$cbydps->map(fn (Cbydp $cbydp): array => ['CBYDP', $cbydp->period(), $cbydp->status->label(), $cbydp->resolution_number, trans_choice(':count priority|:count priorities', $cbydp->priorities_count)]),
                ...$abyips->map(fn (Abyip $abyip): array => ['ABYIP', (string) $abyip->year, $abyip->status->label(), $abyip->resolution_number, trans_choice(':count PPA|:count PPAs', $abyip->ppas->count())]),
            ], empty: 'No plans yet.');

        foreach ($abyips as $abyip) {
            $document->table("ABYIP {$abyip->year} PPAs", [
                'PPA' => 'text',
                'Center of participation' => 'text',
                'Starts' => 'text',
                'Ends' => 'text',
                'Progress %' => 'number',
                'Evidence' => 'number',
                'Budget' => 'money',
                'Spent' => 'money',
                'Status' => 'text',
            ], $abyip->ppas->map(fn (Ppa $ppa): array => [
                $ppa->title,
                $ppa->priority->center->label(),
                $ppa->starts_on->format('M j, Y'),
                $ppa->ends_on->format('M j, Y'),
                $ppa->progress(),
                $ppa->evidence_count,
                (float) $ppa->budget,
                (float) ($ppa->amount_spent ?? 0),
                $ppa->status->label().($ppa->isDelayed() ? ', delayed' : ''),
            ]), summary: [
                'Budget' => '₱'.number_format((float) $abyip->ppas->where('status', '!=', PpaStatus::Cancelled)->sum('budget'), 2),
                'Completed' => $abyip->ppas->where('status', PpaStatus::Completed)->count().' of '.$abyip->ppas->count(),
            ], empty: 'No PPAs listed.');
        }

        $document
            ->table('Sessions', ['Session' => 'text', 'Kind' => 'text', 'Held on' => 'text', 'Quorum' => 'text', 'Resolutions' => 'number'],
                $barangay->meetings()->with('attendees')->withCount('resolutions')->orderByDesc('held_on')->get()->map(fn (Meeting $meeting): array => [
                    $meeting->title,
                    $meeting->kind->label(),
                    $meeting->held_on->format('M j, Y'),
                    match ($meeting->hasQuorum()) {
                        true => 'Yes',
                        false => 'No',
                        null => 'Not recorded',
                    },
                    $meeting->resolutions_count,
                ]), empty: 'No sessions recorded.')
            ->table('Monitoring remarks', ['Date' => 'text', 'From' => 'text', 'Remark' => 'text'],
                $barangay->remarks()->with('author:id,name')->get()->map(fn (MonitoringRemark $remark): array => [
                    $remark->created_at->format('M j, Y'),
                    $remark->author?->name ?? 'SK Federation',
                    $remark->body,
                ]), empty: 'No remarks yet.');

        return DocumentExporter::download($document, $format, 'sk-'.str($barangay->name)->slug().'-'.now()->format('Y-m-d'));
    }
}

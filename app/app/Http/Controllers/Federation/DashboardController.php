<?php

namespace App\Http\Controllers\Federation;

use App\DocumentExporter;
use App\ExportDocument;
use App\ExportFormat;
use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\EventRegistration;
use App\Models\MonitoringRemark;
use App\PlanStatus;
use App\PpaStatus;
use App\UserRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    /**
     * Compare the participating barangays: plans, PPA completion, delays, budget and sessions.
     */
    public function __invoke(): View
    {
        $year = (int) now()->format('Y');
        $barangays = $this->barangays($year);

        $openPpas = $barangays->sum('ppas_count') - $barangays->sum('cancelled_count');

        return view('federation.dashboard', [
            'year' => $year,
            'barangays' => $barangays,
            'monthlyCheckIns' => EventRegistration::monthlyCheckIns($barangays->modelKeys()),
            'latestRemarks' => MonitoringRemark::query()
                ->whereIn('barangay_id', $barangays->modelKeys())
                ->with(['barangay:id,name,logo_path', 'author:id,name'])
                ->latest()
                ->limit(4)
                ->get(),
            'totals' => [
                'ppas' => $barangays->sum('ppas_count'),
                'completionRate' => $openPpas > 0 ? (int) round($barangays->sum('completed_count') / $openPpas * 100) : 0,
                'delayed' => $barangays->sum('delayed_count'),
                'budget' => $barangays->sum('budget_this_year'),
                'youth' => $barangays->sum('youth_count'),
                'participants' => $barangays->sum('participants_this_year'),
            ],
        ]);
    }

    /**
     * Download the compliance table as Excel, PDF, Word or CSV.
     */
    public function export(Request $request): Response
    {
        $format = ExportFormat::from($request->validate([
            'format' => ['nullable', Rule::enum(ExportFormat::class)],
        ])['format'] ?? ExportFormat::Csv->value);
        $year = (int) now()->format('Y');
        $barangays = $this->barangays($year);
        $peso = fn (float $amount): string => '₱'.number_format($amount, 2);

        $document = ExportDocument::make("Barangay compliance, {$year}", 'Plans, PPA progress, budget and sessions of the participating barangays.')
            ->detail('Prepared for', 'SK Federation, Olongapo City')
            ->detail('Barangays', (string) $barangays->count())
            ->table("Compliance {$year}", [
                'Barangay' => 'text',
                'CBYDP approved' => 'text',
                "ABYIP {$year}" => 'text',
                'PPAs' => 'number',
                'Completed' => 'number',
                'Cancelled' => 'number',
                'Delayed' => 'number',
                'Budget' => 'money',
                'Spent' => 'money',
                'Sessions this year' => 'number',
                'Youth accounts' => 'number',
                'Youth who joined events' => 'number',
            ], $barangays->map(fn (Barangay $barangay): array => [
                $barangay->name,
                $barangay->approved_cbydp_count > 0 ? 'Yes' : 'No',
                match (true) {
                    $barangay->approved_abyip_count > 0 => 'Approved',
                    $barangay->draft_abyip_count > 0 => 'Draft',
                    default => 'Not started',
                },
                $barangay->ppas_count,
                $barangay->completed_count,
                $barangay->cancelled_count,
                $barangay->delayed_count,
                (float) ($barangay->budget_this_year ?? 0),
                (float) ($barangay->spent_this_year ?? 0),
                $barangay->meetings_this_year_count,
                $barangay->youth_count,
                $barangay->participants_this_year,
            ]), summary: [
                'Total budget' => $peso((float) $barangays->sum('budget_this_year')),
                'Total spent' => $peso((float) $barangays->sum('spent_this_year')),
                'PPAs completed' => $barangays->sum('completed_count').' of '.$barangays->sum('ppas_count'),
                'Delayed PPAs' => (string) $barangays->sum('delayed_count'),
            ], empty: 'No participating barangays yet.');

        return DocumentExporter::download($document, $format, "compliance-{$year}");
    }

    /**
     * Participating barangays with the counts the compliance table needs.
     *
     * @return Collection<int, Barangay>
     */
    private function barangays(int $year): Collection
    {
        return Barangay::participating()
            ->withCount([
                'ppas',
                'ppas as completed_count' => fn ($query) => $query->where('status', PpaStatus::Completed),
                'ppas as cancelled_count' => fn ($query) => $query->where('status', PpaStatus::Cancelled),
                'ppas as delayed_count' => fn ($query) => $query->delayed(),
                'cbydps as approved_cbydp_count' => fn ($query) => $query->where('status', PlanStatus::Approved)
                    ->where('start_year', '<=', $year)->where('end_year', '>=', $year),
                'abyips as approved_abyip_count' => fn ($query) => $query->where('status', PlanStatus::Approved)->where('year', $year),
                'abyips as draft_abyip_count' => fn ($query) => $query->where('status', PlanStatus::Draft)->where('year', $year),
                'meetings as meetings_this_year_count' => fn ($query) => $query->whereYear('held_on', $year),
                'users as youth_count' => fn ($query) => $query->where('role', UserRole::Youth)->whereNull('deactivated_at'),
                'users as participants_this_year' => fn ($query) => $query->where('role', UserRole::Youth)
                    ->whereHas('eventRegistrations', fn ($registration) => $registration->whereYear('attended_at', $year)),
            ])
            ->withSum(['ppas as spent_this_year' => fn ($query) => $query
                ->whereHas('abyip', fn ($abyip) => $abyip->where('year', $year))], 'amount_spent')
            ->withSum(['ppas as budget_this_year' => fn ($query) => $query
                ->where('status', '!=', PpaStatus::Cancelled)
                ->whereHas('abyip', fn ($abyip) => $abyip->where('year', $year))], 'budget')
            ->withMax('remarks as last_remark_at', 'created_at')
            ->orderBy('name')
            ->get();
    }
}

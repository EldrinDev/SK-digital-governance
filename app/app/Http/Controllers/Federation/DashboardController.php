<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\PlanStatus;
use App\PpaStatus;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Compare the participating barangays: plans, PPA completion, delays, budget and sessions.
     */
    public function __invoke(): View
    {
        $year = (int) now()->format('Y');

        $barangays = Barangay::participating()
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
            ])
            ->withSum(['ppas as budget_this_year' => fn ($query) => $query
                ->where('status', '!=', PpaStatus::Cancelled)
                ->whereHas('abyip', fn ($abyip) => $abyip->where('year', $year))], 'budget')
            ->withMax('remarks as last_remark_at', 'created_at')
            ->orderBy('name')
            ->get();

        $openPpas = $barangays->sum('ppas_count') - $barangays->sum('cancelled_count');

        return view('federation.dashboard', [
            'year' => $year,
            'barangays' => $barangays,
            'totals' => [
                'ppas' => $barangays->sum('ppas_count'),
                'completionRate' => $openPpas > 0 ? (int) round($barangays->sum('completed_count') / $openPpas * 100) : 0,
                'delayed' => $barangays->sum('delayed_count'),
                'budget' => $barangays->sum('budget_this_year'),
            ],
        ]);
    }
}

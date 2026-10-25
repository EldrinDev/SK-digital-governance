<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Milestone;
use App\PpaStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the barangay workspace summary: PPA counts, budget, upcoming milestones and recent activity.
     */
    public function __invoke(Request $request): View
    {
        $barangay = $request->user()->barangay;
        $year = (int) now()->format('Y');

        $currentAbyip = $barangay->abyips()->where('year', $year)->first();
        $ppas = $barangay->ppas();

        return view('officer.dashboard', [
            'barangay' => $barangay,
            'year' => $year,
            'currentAbyip' => $currentAbyip,
            'totalPpas' => (clone $ppas)->count(),
            'completedPpas' => (clone $ppas)->where('status', PpaStatus::Completed)->count(),
            'completedWithEvidence' => (clone $ppas)->where('status', PpaStatus::Completed)->has('evidence')->count(),
            'delayedPpas' => (clone $ppas)->delayed()->count(),
            'plannedBudget' => $currentAbyip?->ppas()->where('status', '!=', PpaStatus::Cancelled)->sum('budget') ?? 0,
            'activePpas' => (clone $ppas)
                ->whereIn('status', [PpaStatus::Planned, PpaStatus::Ongoing])
                ->with(['milestones', 'abyip:id,status'])
                ->orderBy('ends_on')
                ->limit(5)
                ->get(),
            'upcomingMilestones' => Milestone::query()
                ->whereHas('ppa', fn ($query) => $query->where('barangay_id', $barangay->id))
                ->whereNull('completed_at')
                ->with('ppa:id,title')
                ->orderBy('due_on')
                ->limit(6)
                ->get(),
            'remarks' => $barangay->remarks()->with('author:id,name')->limit(3)->get(),
            'recentActivity' => AuditLog::query()
                ->where('barangay_id', $barangay->id)
                ->whereNotIn('action', ['auth.login', 'auth.logout', 'auth.failed', 'auth.lockout'])
                ->with('user:id,name')
                ->latest('created_at')
                ->latest('id')
                ->limit(6)
                ->get(),
        ]);
    }
}

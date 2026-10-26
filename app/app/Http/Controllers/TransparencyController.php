<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\PpaStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The public transparency board. It only ever shows ABYIPs an officer has published,
 * and only evidence an officer has marked public.
 */
class TransparencyController extends Controller
{
    /**
     * List participating barangays with what each has published, plus city-wide totals.
     */
    public function index(): View
    {
        $published = fn ($query) => $query
            ->where('status', '!=', PpaStatus::Cancelled)
            ->whereHas('abyip', fn ($abyip) => $abyip->published());

        $barangays = Barangay::participating()
            ->withCount([
                'abyips as published_abyips_count' => fn ($query) => $query->published(),
                'ppas as published_ppas_count' => $published,
                'ppas as published_completed_count' => fn ($query) => $published($query)->where('status', PpaStatus::Completed),
            ])
            ->withSum(['ppas as published_budget' => $published], 'budget')
            ->withSum(['ppas as published_spent' => $published], 'amount_spent')
            ->withMax(['abyips as latest_published_year' => fn ($query) => $query->published()], 'year')
            ->orderByDesc('published_abyips_count')
            ->orderBy('name')
            ->get();

        return view('transparency.index', [
            'barangays' => $barangays,
            'totals' => [
                'publishing' => $barangays->where('published_abyips_count', '>', 0)->count(),
                'budget' => (float) $barangays->sum('published_budget'),
                'spent' => (float) $barangays->sum('published_spent'),
                'ppas' => $barangays->sum('published_ppas_count'),
                'completed' => $barangays->sum('published_completed_count'),
            ],
        ]);
    }

    /**
     * Show a barangay's published programs with a budget summary, PPA progress and public evidence.
     */
    public function show(Request $request, Barangay $barangay): View
    {
        abort_unless($barangay->is_participating, 404);

        $status = $request->validate(['status' => ['nullable', Rule::in(['ongoing', 'completed', 'planned', 'delayed'])]])['status'] ?? null;

        return view('transparency.show', [
            'barangay' => $barangay,
            'status' => $status,
            'abyips' => $barangay->abyips()
                ->published()
                ->with([
                    'cbydp:id,start_year,end_year',
                    'ppas' => fn ($query) => $query
                        ->with(['priority:id,center,title', 'milestones', 'abyip:id,status', 'evidence' => fn ($evidence) => $evidence->where('is_public', true)])
                        ->orderBy('starts_on'),
                ])
                ->orderByDesc('year')
                ->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\PpaStatus;
use Illuminate\View\View;

/**
 * The public transparency board. It only ever shows ABYIPs an officer has published,
 * and only evidence an officer has marked public.
 */
class TransparencyController extends Controller
{
    /**
     * List participating barangays and how many programs each has published.
     */
    public function index(): View
    {
        return view('transparency.index', [
            'barangays' => Barangay::participating()
                ->withCount(['abyips as published_abyips_count' => fn ($query) => $query->published()])
                ->withSum(['ppas as published_budget' => fn ($query) => $query
                    ->where('status', '!=', PpaStatus::Cancelled)
                    ->whereHas('abyip', fn ($abyip) => $abyip->published())], 'budget')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Show a barangay's published programs with PPA progress and public evidence.
     */
    public function show(Barangay $barangay): View
    {
        abort_unless($barangay->is_participating, 404);

        return view('transparency.show', [
            'barangay' => $barangay,
            'abyips' => $barangay->abyips()
                ->published()
                ->with([
                    'cbydp:id,start_year,end_year',
                    'ppas' => fn ($query) => $query
                        ->with(['priority:id,center,title', 'milestones', 'evidence' => fn ($evidence) => $evidence->where('is_public', true)])
                        ->orderBy('starts_on'),
                ])
                ->orderByDesc('year')
                ->get(),
        ]);
    }
}

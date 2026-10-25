<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use Illuminate\View\View;

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
}

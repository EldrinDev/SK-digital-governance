<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    /**
     * List the barangay's CBYDPs and, under each, its ABYIPs.
     */
    public function index(Request $request): View
    {
        return view('officer.plans.index', [
            'cbydps' => $request->user()->barangay->cbydps()
                ->withCount('priorities')
                ->with(['abyips' => fn ($query) => $query->withCount('ppas')->withSum('ppas', 'budget')->orderByDesc('year')])
                ->orderByDesc('start_year')
                ->get(),
        ]);
    }
}

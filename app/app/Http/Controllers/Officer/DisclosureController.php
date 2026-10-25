<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisclosureController extends Controller
{
    /**
     * Show what is published, what is ready to publish, and what still needs approval.
     */
    public function index(Request $request): View
    {
        $barangay = $request->user()->barangay;

        $abyips = $barangay->abyips()
            ->withCount('ppas')
            ->withSum('ppas', 'budget')
            ->orderByDesc('year')
            ->get();

        return view('officer.disclosure.index', [
            'barangay' => $barangay,
            'published' => $abyips->filter->isPublished(),
            'ready' => $abyips->filter(fn ($abyip) => $abyip->isApproved() && ! $abyip->isPublished()),
            'pending' => $abyips->reject->isApproved(),
            'publicEvidence' => Evidence::where('barangay_id', $barangay->id)->where('is_public', true)->count(),
        ]);
    }
}

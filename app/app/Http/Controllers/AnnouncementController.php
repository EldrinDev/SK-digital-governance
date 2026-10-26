<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public announcements from participating barangays.
 */
class AnnouncementController extends Controller
{
    /**
     * List announcements, optionally for one barangay.
     */
    public function index(Request $request): View
    {
        $barangayId = $request->integer('barangay') ?: null;

        return view('announcements.index', [
            'announcements' => Announcement::query()
                ->visibleToPublic()
                ->when($barangayId, fn ($query) => $query->where('barangay_id', $barangayId))
                ->with('barangay:id,name')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
            'selectedBarangay' => $barangayId,
        ]);
    }

    /**
     * Show one announcement.
     */
    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->barangay->is_participating, 404);

        return view('announcements.show', ['announcement' => $announcement]);
    }
}

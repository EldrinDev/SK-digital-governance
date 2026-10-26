<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the landing page with the latest announcements and upcoming events.
     */
    public function __invoke(): View
    {
        return view('welcome', [
            'announcements' => Announcement::query()->visibleToPublic()->with('barangay:id,name')->latest()->limit(3)->get(),
            'upcomingEvents' => Event::query()
                ->upcoming()
                ->whereHas('barangay', fn ($query) => $query->participating())
                ->with('barangay:id,name')
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),
        ]);
    }
}

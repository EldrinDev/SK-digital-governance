<?php

namespace App\Http\Controllers;

use App\AnnouncementCategory;
use App\CalendarFile;
use App\Models\Announcement;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public announcements from participating barangays.
 */
class AnnouncementController extends Controller
{
    /**
     * List announcements, optionally for one barangay, pinned posts first.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'barangay' => ['nullable', 'integer'],
            'category' => ['nullable', Rule::enum(AnnouncementCategory::class)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $barangayId = (int) ($filters['barangay'] ?? 0) ?: null;
        $category = AnnouncementCategory::tryFrom($filters['category'] ?? '');
        $search = trim($filters['q'] ?? '');

        $matching = Announcement::query()
            ->visibleToPublic()
            ->when($barangayId, fn ($query) => $query->where('barangay_id', $barangayId))
            ->when($search !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('title', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")));

        return view('announcements.index', [
            'announcements' => (clone $matching)
                ->when($category, fn ($query) => $query->where('category', $category))
                ->with('barangay:id,name,logo_path')
                ->pinnedFirst()
                ->paginate(9)
                ->withQueryString(),
            'categoryCounts' => (clone $matching)->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category'),
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
            'selectedBarangay' => $barangayId,
            'selectedCategory' => $category,
            'search' => $search,
            'categories' => AnnouncementCategory::cases(),
        ]);
    }

    /**
     * Show one announcement, with more from the same SK.
     */
    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->barangay->is_participating, 404);

        return view('announcements.show', [
            'announcement' => $announcement,
            'more' => $announcement->barangay->announcements()->with('barangay:id,name,logo_path')->whereKeyNot($announcement->id)->newestFirst()->limit(3)->get(),
        ]);
    }

    /**
     * Download the announced activity as a calendar file, when the caption gives its date.
     */
    public function calendar(Announcement $announcement): Response
    {
        abort_unless($announcement->barangay->is_participating, 404);

        $when = $announcement->happensAt();
        abort_if($when === null, 404);

        return CalendarFile::download(
            uid: 'announcement-'.$announcement->id,
            title: $announcement->title,
            start: $when['start'],
            allDay: $when['allDay'],
            location: trim(($announcement->place() ?? '').', '.$announcement->barangay->name.', Olongapo City', ', '),
            description: "From SK {$announcement->barangay->name}.\n\n".route('announcements.show', $announcement),
            url: route('announcements.show', $announcement),
        );
    }

    /**
     * Serve the post's photo or pubmat.
     */
    public function image(Announcement $announcement): StreamedResponse
    {
        abort_unless($announcement->barangay->is_participating && $announcement->image_path !== null, 404);

        return Storage::response($announcement->image_path, headers: ['Cache-Control' => 'public, max-age=86400']);
    }
}

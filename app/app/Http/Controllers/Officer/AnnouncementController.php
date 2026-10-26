<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Notifications\AnnouncementPosted;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * List the barangay's announcements, newest first.
     */
    public function index(Request $request): View
    {
        return view('officer.announcements.index', [
            'announcements' => $request->user()->barangay->announcements()
                ->with('author:id,name')
                ->latest()
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for a new announcement.
     */
    public function create(): View
    {
        return view('officer.announcements.form', ['announcement' => new Announcement]);
    }

    /**
     * Post the announcement and let the barangay's youth know.
     */
    public function store(Request $request): RedirectResponse
    {
        $barangay = $request->user()->barangay;

        $announcement = new Announcement($this->validated($request));
        $announcement->barangay_id = $barangay->id;
        $announcement->user_id = $request->user()->id;
        $announcement->save();

        AuditLog::record('announcement.posted', $request->user(), ['title' => $announcement->title], $announcement);

        Notification::send(
            $barangay->users()->where('role', UserRole::Youth)->whereNull('deactivated_at')->get(),
            new AnnouncementPosted($announcement),
        );

        return redirect()->route('officer.announcements.index')->with('status', 'Announcement posted. Youth of your barangay were notified.');
    }

    /**
     * Show the form for changing an announcement.
     */
    public function edit(Announcement $announcement): View
    {
        Gate::authorize('update', $announcement);

        return view('officer.announcements.form', ['announcement' => $announcement]);
    }

    /**
     * Save changes to an announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        Gate::authorize('update', $announcement);

        $announcement->update($this->validated($request));

        AuditLog::record('announcement.updated', $request->user(), ['title' => $announcement->title], $announcement);

        return redirect()->route('officer.announcements.index')->with('status', 'Announcement updated.');
    }

    /**
     * Take the announcement down from the public portal.
     */
    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        Gate::authorize('update', $announcement);

        AuditLog::record('announcement.removed', $request->user(), ['title' => $announcement->title], $announcement);

        $announcement->delete();

        return redirect()->route('officer.announcements.index')->with('status', 'Announcement taken down.');
    }

    /**
     * @return array{title: string, body: string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ], [
            'title.required' => 'Give the announcement a headline.',
            'body.required' => 'Write what you want to announce.',
        ]);
    }
}

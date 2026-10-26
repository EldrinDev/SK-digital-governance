<?php

namespace App\Http\Controllers\Officer;

use App\AnnouncementCategory;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Notifications\AnnouncementPosted;
use App\Rules\FacebookUrl;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * List the barangay's announcements, pinned first.
     */
    public function index(Request $request): View
    {
        return view('officer.announcements.index', [
            'announcements' => $request->user()->barangay->announcements()
                ->with(['author:id,name', 'barangay:id,name,logo_path'])
                ->pinnedFirst()
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for a new announcement.
     */
    public function create(Request $request): View
    {
        $announcement = new Announcement(['category' => AnnouncementCategory::Announcement]);
        $announcement->setRelation('barangay', $request->user()->barangay);

        return view('officer.announcements.form', [
            'announcement' => $announcement,
            'categories' => AnnouncementCategory::cases(),
        ]);
    }

    /**
     * Post the announcement and let the barangay's youth know.
     */
    public function store(Request $request): RedirectResponse
    {
        $barangay = $request->user()->barangay;

        $announcement = new Announcement($this->validated($request));
        $this->fillSource($request, $announcement);
        $announcement->barangay_id = $barangay->id;
        $announcement->user_id = $request->user()->id;
        $announcement->pinned_at = $request->boolean('pinned') ? now() : null;

        if ($request->hasFile('image')) {
            $announcement->image_path = $request->file('image')->store("announcements/{$barangay->id}");
        }

        $announcement->save();

        AuditLog::record('announcement.posted', $request->user(), ['title' => $announcement->title], $announcement);

        // Older posts brought over from Facebook are already old news, so youth aren't pinged about them.
        if ($announcement->postedAt()->greaterThan(now()->subDays(3))) {
            Notification::send(
                $barangay->users()->where('role', UserRole::Youth)->whereNull('deactivated_at')->get(),
                new AnnouncementPosted($announcement),
            );

            return redirect()->route('officer.announcements.index')->with('status', 'Announcement posted. Youth of your barangay were notified.');
        }

        return redirect()->route('officer.announcements.index')->with('status', 'Announcement added with its original date. It’s older than three days, so youth weren’t notified.');
    }

    /**
     * Show the form for changing an announcement.
     */
    public function edit(Announcement $announcement): View
    {
        Gate::authorize('update', $announcement);

        return view('officer.announcements.form', [
            'announcement' => $announcement,
            'categories' => AnnouncementCategory::cases(),
        ]);
    }

    /**
     * Save changes to an announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        Gate::authorize('update', $announcement);

        $announcement->fill($this->validated($request));
        $this->fillSource($request, $announcement);
        $announcement->pinned_at = $request->boolean('pinned') ? ($announcement->pinned_at ?? now()) : null;

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $this->deleteImage($announcement);
            $announcement->image_path = $request->hasFile('image')
                ? $request->file('image')->store("announcements/{$announcement->barangay_id}")
                : null;
        }

        $announcement->save();

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

        $this->deleteImage($announcement);
        $announcement->delete();

        return redirect()->route('officer.announcements.index')->with('status', 'Announcement taken down.');
    }

    /**
     * Validate the form. The image is checked here and stored by the caller.
     *
     * @return array{category: string, title: string, body: string}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'category' => ['required', Rule::enum(AnnouncementCategory::class)],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_credit' => ['nullable', 'string', 'max:150'],
            'source_url' => ['nullable', 'string', 'max:500', new FacebookUrl],
            'originally_posted_at' => ['nullable', 'date', 'before_or_equal:now', 'required_with:source_url'],
        ], [
            'originally_posted_at.before_or_equal' => 'The original date can’t be in the future.',
            'originally_posted_at.required_with' => 'Enter when it was posted on Facebook.',
            'title.required' => 'Give the announcement a headline.',
            'body.required' => 'Write the caption.',
            'image.image' => 'Attach a photo or pubmat: JPG, PNG or WebP.',
            'image.mimes' => 'Attach a photo or pubmat: JPG, PNG or WebP.',
            'image.max' => 'The image can be at most 5 MB.',
        ]);

        return Arr::only($validated, ['category', 'title', 'body']);
    }

    /**
     * Record where the post first appeared, when it was brought over from Facebook, and who took the photo.
     */
    private function fillSource(Request $request, Announcement $announcement): void
    {
        $announcement->source_url = $request->filled('source_url') ? $request->string('source_url')->toString() : null;
        $announcement->originally_posted_at = $announcement->source_url !== null ? $request->date('originally_posted_at') : null;
        $announcement->image_credit = $request->filled('image_credit') ? $request->string('image_credit')->toString() : null;
    }

    private function deleteImage(Announcement $announcement): void
    {
        if ($announcement->image_path !== null) {
            Storage::delete($announcement->image_path);
        }
    }
}

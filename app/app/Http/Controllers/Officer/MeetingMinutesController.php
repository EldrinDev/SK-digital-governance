<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetingMinutesController extends Controller
{
    /**
     * Upload the signed minutes, replacing any earlier file.
     */
    public function store(Request $request, Meeting $meeting): RedirectResponse
    {
        Gate::authorize('recordMinutes', $meeting);

        $request->validate([
            'minutes' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'minutes.required' => 'Choose the minutes PDF to upload.',
            'minutes.mimes' => 'Upload the minutes as a PDF.',
            'minutes.max' => 'The PDF must be 10 MB or smaller.',
        ]);

        if ($meeting->minutes_path) {
            Storage::delete($meeting->minutes_path);
        }

        $meeting->minutes_path = $request->file('minutes')->store("minutes/{$meeting->barangay_id}");
        $meeting->save();

        AuditLog::record('meeting.minutes_uploaded', $request->user(), ['meeting' => $meeting->title], $meeting);

        return redirect()->route('officer.meetings.show', $meeting)->with('status', 'Minutes uploaded.');
    }

    /**
     * Download the minutes.
     */
    public function show(Meeting $meeting): StreamedResponse
    {
        Gate::authorize('view', $meeting);
        abort_if($meeting->minutes_path === null, 404);

        return Storage::download($meeting->minutes_path, "Minutes {$meeting->held_on->toDateString()}.pdf");
    }
}

<?php

namespace App\Http\Controllers\Officer;

use App\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MeetingAttendanceController extends Controller
{
    /**
     * Save who was present, absent or excused.
     */
    public function update(Request $request, Meeting $meeting): RedirectResponse
    {
        Gate::authorize('update', $meeting);

        $memberIds = $meeting->barangay->users()->where('role', UserRole::Officer)->pluck('id');

        $validated = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', Rule::enum(AttendanceStatus::class)],
        ], [
            'attendance.required' => 'Mark at least one member.',
        ]);

        $attendance = collect($validated['attendance'])
            ->filter(fn (string $status, int|string $userId): bool => $memberIds->contains((int) $userId))
            ->map(fn (string $status): array => ['status' => $status]);

        $meeting->attendees()->sync($attendance->all());
        $meeting->load('attendees');

        AuditLog::record('meeting.attendance_recorded', $request->user(), [
            'present' => $meeting->presentCount(),
            'recorded' => $meeting->attendees->count(),
        ], $meeting);

        return redirect()->route('officer.meetings.show', $meeting)->with('status', 'Attendance saved.');
    }
}

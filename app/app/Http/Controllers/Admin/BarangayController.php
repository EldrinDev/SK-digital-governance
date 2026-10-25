<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarangayController extends Controller
{
    /**
     * List barangays with whether they take part in the pilot.
     */
    public function index(): View
    {
        return view('admin.barangays.index', [
            'barangays' => Barangay::query()
                ->withCount([
                    'users as officers_count' => fn ($query) => $query->where('role', UserRole::Officer),
                    'users as youth_count' => fn ($query) => $query->where('role', UserRole::Youth),
                ])
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Turn a barangay's participation on or off.
     */
    public function update(Request $request, Barangay $barangay): RedirectResponse
    {
        $validated = $request->validate([
            'is_participating' => ['required', 'boolean'],
        ]);

        $barangay->update($validated);

        AuditLog::record('barangay.updated', $request->user(), [
            'is_participating' => $barangay->is_participating,
        ], $barangay);

        $state = $barangay->is_participating ? 'now takes part in' : 'no longer takes part in';

        return redirect()->route('admin.barangays.index')->with('status', "{$barangay->name} {$state} the platform.");
    }
}

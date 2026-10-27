<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YouthIdController extends Controller
{
    /**
     * Show who a scanned digital ID belongs to. Only SK staff can open it; officers of the
     * youth's own barangay can also check them in to today's events from here.
     */
    public function __invoke(Request $request, string $code): View
    {
        $youth = User::query()
            ->where('member_code', $code)
            ->where('role', UserRole::Youth)
            ->with('barangay')
            ->first();

        if ($youth === null) {
            return view('youth-id.verify', ['youth' => null, 'code' => $code, 'checkIns' => collect()]);
        }

        $viewer = $request->user();
        $sameBarangay = $viewer->isOfficer() && $viewer->barangay_id === $youth->barangay_id;

        AuditLog::record('youth_id.verified', $viewer, ['youth' => $youth->name], $youth);

        return view('youth-id.verify', [
            'youth' => $youth,
            'code' => $code,
            'attendedCount' => $youth->eventRegistrations()->whereNotNull('attended_at')->count(),
            // Today's events of the officer's barangay that this youth registered for.
            'checkIns' => $sameBarangay
                ? $youth->eventRegistrations()
                    ->with('event')
                    ->whereHas('event', fn ($event) => $event->where('barangay_id', $viewer->barangay_id)->whereDate('starts_at', today()))
                    ->get()
                : collect(),
            'canCheckIn' => $sameBarangay,
        ]);
    }
}

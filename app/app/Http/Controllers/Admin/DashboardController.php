<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\User;
use App\UserRole;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show account totals, participating barangays and recent activity.
     */
    public function __invoke(): View
    {
        $accountsByRole = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.dashboard', [
            'accountsByRole' => collect(UserRole::cases())
                ->mapWithKeys(fn (UserRole $role): array => [$role->pluralLabel() => (int) ($accountsByRole[$role->value] ?? 0)]),
            'deactivatedCount' => User::whereNotNull('deactivated_at')->count(),
            'participatingBarangays' => Barangay::participating()->orderBy('name')->pluck('name'),
            'barangayCount' => Barangay::count(),
            'failedLoginsToday' => AuditLog::where('action', 'auth.failed')->whereDate('created_at', today())->count(),
            'recentEntries' => AuditLog::with(['user:id,name', 'barangay:id,name'])->latest('created_at')->latest('id')->limit(8)->get(),
        ]);
    }
}

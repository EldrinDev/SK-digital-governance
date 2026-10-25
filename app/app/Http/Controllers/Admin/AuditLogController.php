<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Show the audit trail, newest first, with filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'action' => ['nullable', Rule::in(array_keys(AuditLog::ACTIONS))],
            'search' => ['nullable', 'string', 'max:100'],
            'barangay' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $entries = AuditLog::query()
            ->with(['user:id,name,email', 'barangay:id,name'])
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['barangay'] ?? null, fn ($query, int $barangay) => $query->where('barangay_id', $barangay))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'entries' => $entries,
            'filters' => $filters,
            'barangays' => Barangay::orderBy('name')->get(['id', 'name']),
        ]);
    }
}

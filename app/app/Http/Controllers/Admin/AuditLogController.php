<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    use StreamsCsv;

    /**
     * Show the audit trail, newest first, with filters.
     */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.audit-logs.index', [
            'entries' => $this->query($filters)->paginate(25)->withQueryString(),
            'filters' => $filters,
            'barangays' => Barangay::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Download the filtered audit trail as a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        $entries = $this->query($this->filters($request))->lazy();

        return $this->csv('audit-trail-'.now()->format('Y-m-d').'.csv',
            ['When', 'Action', 'Person', 'Email', 'Barangay', 'Details', 'IP address'],
            $entries->map(fn (AuditLog $entry): array => [
                $entry->created_at->toDateTimeString(),
                $entry->label(),
                $entry->user?->name,
                $entry->user?->email ?? ($entry->properties['email'] ?? null),
                $entry->barangay?->name,
                collect($entry->properties ?? [])->map(fn ($value, $key) => $key.': '.(is_array($value) ? implode(', ', $value) : $value))->implode('; '),
                $entry->ip_address,
            ]),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return $request->validate([
            'action' => ['nullable', Rule::in(array_keys(AuditLog::ACTIONS))],
            'search' => ['nullable', 'string', 'max:100'],
            'barangay' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<AuditLog>
     */
    private function query(array $filters): Builder
    {
        return AuditLog::query()
            ->with(['user:id,name,email', 'barangay:id,name'])
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['barangay'] ?? null, fn ($query, int $barangay) => $query->where('barangay_id', $barangay))
            ->when($filters['from'] ?? null, fn ($query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->latest('id');
    }
}

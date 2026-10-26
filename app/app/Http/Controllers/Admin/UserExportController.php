<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\StreamsCsv;
use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportController extends Controller
{
    use StreamsCsv;

    /**
     * Download every account with its role, workspace and status.
     */
    public function __invoke(): StreamedResponse
    {
        return $this->csv('accounts-'.now()->format('Y-m-d').'.csv',
            ['Name', 'Email', 'Role', 'Barangay', 'Status', 'Created', 'Privacy notice accepted'],
            User::with('barangay:id,name')->orderBy('name')->lazy()->map(fn (User $user): array => [
                $user->name,
                $user->email,
                $user->role->label(),
                $user->barangay?->name ?? 'City-wide',
                $user->isActive() ? 'Active' : 'Deactivated',
                $user->created_at->toDateString(),
                $user->privacy_consented_at?->toDateString(),
            ]),
        );
    }
}

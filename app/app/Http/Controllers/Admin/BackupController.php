<?php

namespace App\Http\Controllers\Admin;

use App\BackupManager;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(private BackupManager $backups) {}

    /**
     * List backups and offer to create or restore one.
     */
    public function index(): View
    {
        return view('admin.backups.index', [
            'supported' => $this->backups->isSupported(),
            'backups' => $this->backups->all(),
        ]);
    }

    /**
     * Create a backup now.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $name = $this->backups->create();
        } catch (RuntimeException $exception) {
            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        AuditLog::record('backup.created', $request->user(), ['file' => $name]);

        return back()->with('status', "Backup {$name} created.");
    }

    /**
     * Download a backup archive.
     */
    public function show(string $backup): BinaryFileResponse
    {
        try {
            return response()->download($this->backups->path($backup));
        } catch (RuntimeException) {
            abort(404);
        }
    }

    /**
     * Delete a backup archive.
     */
    public function destroy(Request $request, string $backup): RedirectResponse
    {
        try {
            $this->backups->delete($backup);
        } catch (RuntimeException) {
            abort(404);
        }

        AuditLog::record('backup.deleted', $request->user(), ['file' => $backup]);

        return back()->toast("Backup {$backup} deleted.", 'warning');
    }

    /**
     * Restore everything from a backup. Signs everyone out, since sessions are restored too.
     */
    public function restore(Request $request, string $backup): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:RESTORE'],
        ], [
            'confirmation.required' => 'Type RESTORE to confirm.',
            'confirmation.in' => 'Type RESTORE in capital letters to confirm.',
        ]);

        $admin = $request->user();

        try {
            $safety = $this->backups->restore($backup);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['backup' => $exception->getMessage()]);
        }

        AuditLog::record('backup.restored', null, ['file' => $backup, 'safety_backup' => $safety, 'by' => $admin->email]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login')->toast("Restored from {$backup}. A copy of the data before the restore was saved as {$safety}. Please log in again.", 'warning');
    }
}

<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DigitalIdController extends Controller
{
    /**
     * Show the youth's digital Katipunan ng Kabataan ID full screen, ready to show or print.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        return view('account.digital-id', [
            'user' => $user,
            'code' => $user->memberCode(),
        ]);
    }

    /**
     * Replace the ID code, for a lost phone or a leaked screenshot. The old QR stops working.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->user()->renewMemberCode();

        AuditLog::record('account.id_renewed', $request->user());

        return back()->toast('Your digital ID was replaced. The old QR code no longer works.', 'warning');
    }
}

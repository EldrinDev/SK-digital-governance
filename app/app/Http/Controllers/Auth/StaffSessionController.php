<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Models\Barangay;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffSessionController extends Controller
{
    /**
     * Show the SK access login page.
     */
    public function create(): View
    {
        return view('auth.staff-login', [
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Log a staff member in to the chosen workspace.
     */
    public function store(StaffLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->role->homeRoute(), absolute: false));
    }
}

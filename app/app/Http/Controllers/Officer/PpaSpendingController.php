<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ppa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PpaSpendingController extends Controller
{
    /**
     * Record how much the PPA has spent so far, as encoded by the Treasurer.
     * The platform tracks the figure only; disbursement stays in the barangay's books.
     */
    public function update(Request $request, Ppa $ppa): RedirectResponse
    {
        Gate::authorize('recordSpending', $ppa);

        $validated = $request->validate([
            'amount_spent' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ], [
            'amount_spent.required' => 'Enter the amount spent so far.',
            'amount_spent.min' => 'The amount can’t be negative.',
            'amount_spent.decimal' => 'Use at most two decimal places.',
        ]);

        $previous = $ppa->amount_spent;
        $ppa->forceFill([
            'amount_spent' => $validated['amount_spent'],
            'spending_recorded_at' => now(),
        ])->save();

        AuditLog::record('ppa.spending_recorded', $request->user(), [
            'from' => $previous,
            'to' => $ppa->amount_spent,
        ], $ppa);

        $message = 'Spending recorded.';

        if ($ppa->isOverBudget()) {
            $message .= ' It is above the PPA’s budget, so check that an amendment or realignment was approved.';
        }

        return redirect()->route('officer.ppas.show', $ppa)->with('status', $message);
    }
}

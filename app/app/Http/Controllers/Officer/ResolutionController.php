<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\Resolution;
use App\ResolutionOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResolutionController extends Controller
{
    /**
     * Show the register of resolutions.
     */
    public function index(Request $request): View
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;

        return view('officer.resolutions.index', [
            'resolutions' => $request->user()->barangay->resolutions()
                ->with('meeting:id,title,held_on')
                ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                    ->where('number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")))
                ->orderByDesc('adopted_on')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Add a resolution to the register, usually from the session that adopted it.
     */
    public function store(Request $request): RedirectResponse
    {
        $barangay = $request->user()->barangay;

        $validated = $request->validate([
            'meeting_id' => ['nullable', Rule::exists(Meeting::class, 'id')->where('barangay_id', $barangay->id)],
            'number' => ['required', 'string', 'max:100', Rule::unique(Resolution::class)->where('barangay_id', $barangay->id)],
            'title' => ['required', 'string', 'max:255'],
            'outcome' => ['required', Rule::enum(ResolutionOutcome::class)],
            'adopted_on' => ['required', 'date', 'before_or_equal:today'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'number.required' => 'Enter the resolution number.',
            'number.unique' => 'Your register already has a resolution with this number.',
            'title.required' => 'Enter what the resolution is about.',
            'adopted_on.before_or_equal' => 'The date can’t be in the future.',
            'file.mimes' => 'Attach the signed resolution as a PDF.',
        ]);

        $resolution = new Resolution(collect($validated)->except('file')->all());
        $resolution->barangay_id = $barangay->id;

        if ($request->hasFile('file')) {
            $resolution->file_path = $request->file('file')->store("resolutions/{$barangay->id}");
        }

        $resolution->save();

        AuditLog::record('resolution.recorded', $request->user(), ['number' => $resolution->number, 'outcome' => $resolution->outcome->label()], $resolution);

        $back = $resolution->meeting_id
            ? route('officer.meetings.show', $resolution->meeting_id)
            : route('officer.resolutions.index');

        return redirect($back)->with('status', "{$resolution->number} added to the register.");
    }

    /**
     * Download the signed resolution.
     */
    public function show(Resolution $resolution): StreamedResponse
    {
        Gate::authorize('view', $resolution);
        abort_if($resolution->file_path === null, 404);

        return Storage::download($resolution->file_path, "{$resolution->number}.pdf");
    }
}

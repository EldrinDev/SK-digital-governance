<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Officer\PpaRequest;
use App\Models\Abyip;
use App\Models\AuditLog;
use App\Models\Ppa;
use App\PpaStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PpaController extends Controller
{
    /**
     * List every PPA in the barangay, with filters for year and status.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in([...array_column(PpaStatus::cases(), 'value'), 'delayed'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $barangay = $request->user()->barangay;

        $ppas = $barangay->ppas()
            ->with(['abyip:id,year,status', 'priority:id,center,title', 'milestones'])
            ->when($filters['year'] ?? null, fn ($query, int $year) => $query->whereHas('abyip', fn ($abyip) => $abyip->where('year', $year)))
            ->when(($filters['status'] ?? null) === 'delayed', fn ($query) => $query->delayed())
            ->when(in_array($filters['status'] ?? null, array_column(PpaStatus::cases(), 'value'), true), fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->orderBy('starts_on')
            ->paginate(20)
            ->withQueryString();

        return view('officer.ppas.index', [
            'ppas' => $ppas,
            'filters' => $filters,
            'years' => $barangay->abyips()->orderByDesc('year')->pluck('year'),
        ]);
    }

    /**
     * Show the form for adding a PPA to a draft ABYIP.
     */
    public function create(Abyip $abyip): View
    {
        Gate::authorize('update', $abyip);

        return view('officer.ppas.create', [
            'abyip' => $abyip,
            'priorities' => $abyip->cbydp->priorities()->orderBy('center')->orderBy('title')->get(),
        ]);
    }

    /**
     * Add the PPA to the ABYIP.
     */
    public function store(PpaRequest $request, Abyip $abyip): RedirectResponse
    {
        $ppa = new Ppa($request->validated());
        $ppa->barangay_id = $abyip->barangay_id;
        $abyip->ppas()->save($ppa);

        AuditLog::record('ppa.created', $request->user(), ['title' => $ppa->title, 'budget' => $ppa->budget], $ppa);

        return redirect()->route('officer.abyips.show', $abyip)->with('status', "“{$ppa->title}” was added to ABYIP {$abyip->year}.");
    }

    /**
     * Show the PPA with its milestones and status.
     */
    public function show(Ppa $ppa): View
    {
        Gate::authorize('view', $ppa);

        $ppa->load(['abyip.cbydp', 'priority', 'milestones', 'evidence.uploader:id,name']);

        return view('officer.ppas.show', [
            'ppa' => $ppa,
            'activity' => AuditLog::query()
                ->where('subject_type', $ppa->getMorphClass())
                ->where('subject_id', $ppa->id)
                ->with('user:id,name')
                ->latest('created_at')
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Show the form for changing a PPA in a draft ABYIP.
     */
    public function edit(Ppa $ppa): View
    {
        Gate::authorize('update', $ppa);

        return view('officer.ppas.edit', [
            'ppa' => $ppa,
            'abyip' => $ppa->abyip,
            'priorities' => $ppa->abyip->cbydp->priorities()->orderBy('center')->orderBy('title')->get(),
        ]);
    }

    /**
     * Save changes to the PPA's planned details.
     */
    public function update(PpaRequest $request, Ppa $ppa): RedirectResponse
    {
        $ppa->fill($request->validated());
        $changed = array_keys($ppa->getDirty());
        $ppa->save();

        if ($changed !== []) {
            AuditLog::record('ppa.updated', $request->user(), ['changed' => $changed], $ppa);
        }

        return redirect()->route('officer.ppas.show', $ppa)->with('status', 'PPA updated.');
    }

    /**
     * Remove the PPA from its draft ABYIP.
     */
    public function destroy(Request $request, Ppa $ppa): RedirectResponse
    {
        Gate::authorize('delete', $ppa);

        $abyip = $ppa->abyip;
        $ppa->delete();

        AuditLog::record('ppa.deleted', $request->user(), ['title' => $ppa->title], $abyip);

        return redirect()->route('officer.abyips.show', $abyip)->with('status', "“{$ppa->title}” was removed.");
    }
}

<?php

namespace App\Http\Controllers;

use App\CenterOfParticipation;
use App\Models\Barangay;
use App\Models\Ppa;
use App\PpaStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The SK's programs (PPAs) for youth: what each barangay is doing, how far along it is,
 * and which of its events they can join. Only programs in published ABYIPs appear.
 */
class ProgramController extends Controller
{
    /**
     * List programs, filtered by barangay, area (center of participation) and status.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'barangay' => ['nullable', 'integer'],
            'area' => ['nullable', Rule::enum(CenterOfParticipation::class)],
            'status' => ['nullable', Rule::in(['ongoing', 'upcoming', 'completed'])],
        ]);
        $barangayId = (int) ($filters['barangay'] ?? 0) ?: null;
        $area = CenterOfParticipation::tryFrom($filters['area'] ?? '');
        $status = $filters['status'] ?? null;

        $visible = Ppa::query()
            ->publiclyVisible()
            ->when($barangayId, fn ($query) => $query->where('barangay_id', $barangayId))
            ->when($area, fn ($query) => $query->whereHas('priority', fn ($priority) => $priority->where('center', $area)));

        return view('programs.index', [
            'programs' => (clone $visible)
                ->when($status === 'ongoing', fn ($query) => $query->where('status', PpaStatus::Ongoing))
                ->when($status === 'upcoming', fn ($query) => $query->where('status', PpaStatus::Planned))
                ->when($status === 'completed', fn ($query) => $query->where('status', PpaStatus::Completed))
                ->with([
                    'barangay:id,name,logo_path',
                    'priority:id,center',
                    'abyip:id,status',
                    'milestones',
                    'evidence' => fn ($evidence) => $evidence->where('is_public', true)->where('mime_type', 'like', 'image/%'),
                ])
                ->withCount(['events as open_events_count' => fn ($events) => $events->upcoming()])
                ->orderByRaw("case status when 'ongoing' then 0 when 'planned' then 1 else 2 end")
                ->orderBy('starts_on')
                ->paginate(9)
                ->withQueryString(),
            'statusCounts' => [
                'ongoing' => (clone $visible)->where('status', PpaStatus::Ongoing)->count(),
                'upcoming' => (clone $visible)->where('status', PpaStatus::Planned)->count(),
                'completed' => (clone $visible)->where('status', PpaStatus::Completed)->count(),
            ],
            'areas' => CenterOfParticipation::cases(),
            'barangays' => Barangay::participating()->orderBy('name')->get(['id', 'name']),
            'selectedBarangay' => $barangayId,
            'selectedArea' => $area,
            'selectedStatus' => $status,
        ]);
    }

    /**
     * Show one program: what it aims for, its progress, events to join and photos.
     */
    public function show(Ppa $program): View
    {
        abort_unless(Ppa::query()->publiclyVisible()->whereKey($program->id)->exists(), 404);

        $program->load([
            'barangay',
            'priority:id,center,title',
            'abyip:id,year,status,resolution_number',
            'milestones' => fn ($milestones) => $milestones->orderBy('due_on'),
            'evidence' => fn ($evidence) => $evidence->where('is_public', true)->latest(),
        ]);

        return view('programs.show', [
            'program' => $program,
            'upcomingEvents' => $program->events()->upcoming()->withCount('registrations')->orderBy('starts_at')->get(),
            'pastEventsCount' => $program->events()->where('starts_at', '<=', now())->count(),
            'more' => Ppa::query()
                ->publiclyVisible()
                ->where('barangay_id', $program->barangay_id)
                ->whereKeyNot($program->id)
                ->with(['priority:id,center', 'milestones', 'abyip:id,status'])
                ->orderByRaw("case status when 'ongoing' then 0 when 'planned' then 1 else 2 end")
                ->limit(3)
                ->get(),
        ]);
    }
}

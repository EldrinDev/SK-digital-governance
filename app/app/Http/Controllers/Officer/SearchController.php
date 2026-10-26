<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * How many matches to show for each kind of record.
     */
    private const int PER_GROUP = 8;

    /**
     * Search the barangay's records: PPAs, evidence, meetings, resolutions, events and announcements.
     */
    public function __invoke(Request $request): View
    {
        $term = trim($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? '');
        $barangay = $request->user()->barangay;
        $results = [];

        if (Str::length($term) >= 2) {
            $results = [
                'PPAs' => $this->find($barangay->ppas()->with('abyip:id,year'), ['title', 'description', 'target'], $term)
                    ->map(fn ($ppa): array => ['title' => $ppa->title, 'meta' => "ABYIP {$ppa->abyip->year} · {$ppa->status->label()}", 'url' => route('officer.ppas.show', $ppa)]),
                'Evidence and documents' => $this->find(Evidence::query()->where('barangay_id', $barangay->id)->with('ppa:id,title'), ['title', 'original_name'], $term)
                    ->map(fn ($evidence): array => ['title' => $evidence->title, 'meta' => "{$evidence->original_name} · {$evidence->ppa->title}", 'url' => route('officer.ppas.show', $evidence->ppa_id)]),
                'Sessions' => $this->find($barangay->meetings(), ['title', 'agenda', 'venue'], $term)
                    ->map(fn ($meeting): array => ['title' => $meeting->title, 'meta' => $meeting->held_on->format('M j, Y'), 'url' => route('officer.meetings.show', $meeting)]),
                'Resolutions' => $this->find($barangay->resolutions(), ['number', 'title'], $term)
                    ->map(fn ($resolution): array => ['title' => "{$resolution->number}: {$resolution->title}", 'meta' => $resolution->outcome->label().' · '.$resolution->adopted_on->format('M j, Y'), 'url' => route('officer.resolutions.index', ['search' => $resolution->number])]),
                'Events' => $this->find($barangay->events(), ['title', 'description', 'venue'], $term)
                    ->map(fn ($event): array => ['title' => $event->title, 'meta' => $event->starts_at->format('M j, Y').' · '.$event->venue, 'url' => route('officer.events.show', $event)]),
                'Announcements' => $this->find($barangay->announcements(), ['title', 'body'], $term)
                    ->map(fn ($announcement): array => ['title' => $announcement->title, 'meta' => $announcement->created_at->format('M j, Y'), 'url' => route('officer.announcements.edit', $announcement)]),
            ];

            $results = array_filter($results, fn ($group): bool => $group->isNotEmpty());
        }

        return view('officer.search', ['term' => $term, 'results' => $results]);
    }

    /**
     * Get the newest records whose given columns contain the term.
     *
     * @param  Builder<Model>|Relation<Model, Model, mixed>  $query
     * @param  list<string>  $columns
     * @return Collection<int, Model>
     */
    private function find(Builder|Relation $query, array $columns, string $term): Collection
    {
        return $query
            ->where(function ($inner) use ($columns, $term): void {
                foreach ($columns as $column) {
                    $inner->orWhere($column, 'like', "%{$term}%");
                }
            })
            ->latest()
            ->limit(self::PER_GROUP)
            ->get();
    }
}

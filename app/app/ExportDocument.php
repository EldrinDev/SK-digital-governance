<?php

namespace App;

/**
 * A report described once — title, details and tables — so the same content can be
 * downloaded as Excel, PDF, Word or CSV by DocumentExporter.
 */
class ExportDocument
{
    /**
     * @var array<string, string>
     */
    public array $details = [];

    /**
     * @var list<array{heading: string, summary: array<string, string>, columns: array<string, string>, rows: list<list<mixed>>, empty: string}>
     */
    public array $sections = [];

    public function __construct(public string $title, public string $subtitle = '') {}

    public static function make(string $title, string $subtitle = ''): self
    {
        return new self($title, $subtitle);
    }

    /**
     * A labelled fact shown under the title, such as the barangay or who prepared it.
     */
    public function detail(string $label, ?string $value): self
    {
        if ($value !== null && $value !== '') {
            $this->details[$label] = $value;
        }

        return $this;
    }

    /**
     * Add a section with a table. Columns map each heading to its type: text, number or money.
     *
     * @param  array<string, string>  $columns
     * @param  iterable<array<int, mixed>>  $rows
     * @param  array<string, string>  $summary  Labelled figures shown above the table.
     */
    public function table(string $heading, array $columns, iterable $rows, array $summary = [], string $empty = 'Nothing to show.'): self
    {
        $this->sections[] = [
            'heading' => $heading,
            'summary' => $summary,
            'columns' => $columns,
            'rows' => collect($rows)->map(fn ($row): array => array_values((array) $row))->values()->all(),
            'empty' => $empty,
        ];

        return $this;
    }

    /**
     * Whether a table is wide enough to need a landscape page.
     */
    public function isWide(): bool
    {
        return collect($this->sections)->contains(fn (array $section): bool => count($section['columns']) > 6);
    }
}

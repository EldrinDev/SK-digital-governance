<?php

namespace App;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory as WordFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings as WordSettings;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns an ExportDocument into a download in the format the person picked.
 */
class DocumentExporter
{
    private const NAVY = '0D1F38';

    private const LINE = 'D9DEE5';

    private const MONEY_FORMAT = '"₱"#,##0.00';

    public function __construct(private ExportDocument $document) {}

    /**
     * Download the document as "{basename}.{extension}".
     */
    public static function download(ExportDocument $document, ExportFormat $format, string $basename): Response
    {
        $exporter = new self($document);
        $filename = "{$basename}.{$format->value}";

        if ($format === ExportFormat::Pdf) {
            return $exporter->pdf()->download($filename);
        }

        return response()->streamDownload(match ($format) {
            ExportFormat::Excel => $exporter->writeExcel(...),
            ExportFormat::Word => $exporter->writeWord(...),
            ExportFormat::Csv => $exporter->writeCsv(...),
        }, $filename, ['Content-Type' => $format->mimeType()]);
    }

    /**
     * The line saying where the file came from and when.
     */
    private function footnote(): string
    {
        $who = auth()->user()?->name;

        return 'SK Digital Governance, Olongapo City. Generated '.now()->format('F j, Y, g:i A').($who ? " by {$who}" : '').'.';
    }

    /**
     * One worksheet per table, each with the report header, a bold header row,
     * filters, a frozen header and peso formatting.
     */
    public function writeExcel(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()->setCreator('SK Digital Governance')->setTitle($this->document->title);
        $spreadsheet->removeSheetByIndex(0);
        $usedNames = [];

        foreach ($this->document->sections as $section) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->sheetName($section['heading'], $usedNames));
            $this->fillSheet($sheet, $section);
        }

        $spreadsheet->setActiveSheetIndex(0);
        (new Xlsx($spreadsheet))->save('php://output');
        $spreadsheet->disconnectWorksheets();
    }

    /**
     * @param  array{heading: string, summary: array<string, string>, columns: array<string, string>, rows: list<list<mixed>>, empty: string}  $section
     */
    private function fillSheet(Worksheet $sheet, array $section): void
    {
        $columnCount = max(2, count($section['columns']));
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $row = 1;

        $sheet->setCellValueExplicit([1, $row], $this->document->title, DataType::TYPE_STRING);
        $sheet->getStyle([1, $row])->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::NAVY);
        $row++;

        if ($this->document->subtitle !== '') {
            $sheet->setCellValueExplicit([1, $row++], $this->document->subtitle, DataType::TYPE_STRING);
        }

        foreach ($this->document->details + $section['summary'] as $label => $value) {
            $sheet->setCellValueExplicit([1, $row], $label, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit([2, $row], $value, DataType::TYPE_STRING);
            $sheet->getStyle([1, $row])->getFont()->setBold(true);
            $row++;
        }

        $row++;
        $headerRow = $row;

        foreach (array_keys($section['columns']) as $index => $heading) {
            $sheet->setCellValueExplicit([$index + 1, $row], $heading, DataType::TYPE_STRING);
        }

        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::NAVY]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $types = array_values($section['columns']);

        foreach ($section['rows'] as $values) {
            $row++;

            foreach ($values as $index => $value) {
                $type = $types[$index] ?? 'text';

                if ($type === 'text' || ! is_numeric($value)) {
                    $sheet->setCellValueExplicit([$index + 1, $row], (string) ($value ?? ''), DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValueExplicit([$index + 1, $row], (float) $value, DataType::TYPE_NUMERIC);
                    $sheet->getStyle([$index + 1, $row])->getNumberFormat()->setFormatCode($type === 'money' ? self::MONEY_FORMAT : '#,##0');
                }
            }
        }

        if ($section['rows'] === []) {
            $row++;
            $sheet->setCellValueExplicit([1, $row], $section['empty'], DataType::TYPE_STRING);
        } else {
            $sheet->setAutoFilter("A{$headerRow}:{$lastColumn}{$row}");
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::LINE);
        }

        $sheet->setCellValueExplicit([1, $row + 2], $this->footnote(), DataType::TYPE_STRING);
        $sheet->getStyle([1, $row + 2])->getFont()->setItalic(true)->setSize(9);
        $sheet->freezePane('A'.($headerRow + 1));

        foreach (range(1, $columnCount) as $index) {
            $sheet->getColumnDimensionByColumn($index)->setAutoSize(true);
        }
    }

    /**
     * A worksheet name Excel accepts: at most 31 characters, none of []:*?/\ and unique.
     *
     * @param  list<string>  $usedNames
     */
    private function sheetName(string $heading, array &$usedNames): string
    {
        $base = mb_substr(trim(preg_replace('/[\[\]:*?\/\\\\]/', ' ', $heading)) ?: 'Sheet', 0, 28);
        $name = $base;

        for ($copy = 2; in_array(mb_strtolower($name), $usedNames, true); $copy++) {
            $name = "{$base} {$copy}";
        }

        $usedNames[] = mb_strtolower($name);

        return $name;
    }

    /**
     * A printable PDF, landscape when a table is wide.
     */
    public function pdf(): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('exports.document', [
            'document' => $this->document,
            'format' => $this->display(...),
            'footnote' => $this->footnote(),
        ])
            ->setOption('isFontSubsettingEnabled', true)
            ->setPaper('a4', $this->document->isWide() ? 'landscape' : 'portrait');
    }

    /**
     * An editable Word document with the same header and tables.
     */
    public function writeWord(): void
    {
        WordSettings::setOutputEscapingEnabled(true);

        $word = new PhpWord;
        $word->getDocInfo()->setCreator('SK Digital Governance')->setTitle($this->document->title);
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(10);
        $word->addTableStyle('export', ['borderSize' => 4, 'borderColor' => self::LINE, 'cellMargin' => 70], ['bgColor' => self::NAVY]);

        $page = $word->addSection($this->document->isWide() ? ['orientation' => 'landscape'] : []);
        $page->addText('SK Digital Governance, Olongapo City', ['size' => 9, 'color' => '64748B']);
        $page->addText($this->document->title, ['size' => 18, 'bold' => true, 'color' => self::NAVY], ['spaceAfter' => 60]);

        if ($this->document->subtitle !== '') {
            $page->addText($this->document->subtitle, ['color' => '475569'], ['spaceAfter' => 120]);
        }

        foreach ($this->document->details as $label => $value) {
            $line = $page->addTextRun(['spaceAfter' => 0]);
            $line->addText("{$label}: ", ['bold' => true]);
            $line->addText($value);
        }

        foreach ($this->document->sections as $section) {
            $page->addText($section['heading'], ['size' => 13, 'bold' => true, 'color' => self::NAVY], ['spaceBefore' => 280, 'spaceAfter' => 100]);

            foreach ($section['summary'] as $label => $value) {
                $line = $page->addTextRun(['spaceAfter' => 0]);
                $line->addText("{$label}: ", ['bold' => true]);
                $line->addText($value);
            }

            if ($section['rows'] === []) {
                $page->addText($section['empty'], ['italic' => true, 'color' => '64748B']);

                continue;
            }

            if ($section['summary'] !== []) {
                $page->addTextBreak();
            }

            $types = array_values($section['columns']);
            $table = $page->addTable('export');
            $table->addRow(null, ['tblHeader' => true]);

            foreach (array_keys($section['columns']) as $index => $heading) {
                $table->addCell()->addText($heading, ['bold' => true, 'color' => 'FFFFFF'], ['alignment' => $this->isNumeric($types[$index]) ? Jc::END : Jc::START, 'spaceAfter' => 0]);
            }

            foreach ($section['rows'] as $values) {
                $table->addRow(null, ['cantSplit' => true]);

                foreach ($values as $index => $value) {
                    $type = $types[$index] ?? 'text';
                    $table->addCell()->addText($this->display($value, $type), null, ['alignment' => $this->isNumeric($type) ? Jc::END : Jc::START, 'spaceAfter' => 0]);
                }
            }
        }

        $page->addText($this->footnote(), ['size' => 8, 'italic' => true, 'color' => '64748B'], ['spaceBefore' => 360]);

        WordFactory::createWriter($word, 'Word2007')->save('php://output');
    }

    /**
     * Plain rows for other systems. A single table is written as is; several tables
     * are separated by their heading and a blank line.
     */
    public function writeCsv(): void
    {
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        $hasManyTables = count($this->document->sections) > 1;

        foreach ($this->document->sections as $index => $section) {
            if ($hasManyTables) {
                if ($index > 0) {
                    fputcsv($out, []);
                }

                fputcsv($out, [$this->safeCell($section['heading'])]);
            }

            fputcsv($out, array_map($this->safeCell(...), array_keys($section['columns'])));

            foreach ($section['rows'] as $values) {
                fputcsv($out, array_map($this->safeCell(...), $values));
            }
        }

        fclose($out);
    }

    /**
     * Stop spreadsheet apps from running a cell as a formula (CSV injection).
     */
    private function safeCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }

    /**
     * A value as people read it: pesos with centavos, numbers with thousands separators.
     */
    public function display(mixed $value, string $type = 'text'): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match (true) {
            $type === 'money' && is_numeric($value) => '₱'.number_format((float) $value, 2),
            $type === 'number' && is_numeric($value) => number_format((float) $value),
            default => (string) $value,
        };
    }

    private function isNumeric(?string $type): bool
    {
        return in_array($type, ['number', 'money'], true);
    }
}

<?php

namespace App;

/**
 * The file types a report can be downloaded as.
 */
enum ExportFormat: string
{
    case Excel = 'xlsx';
    case Pdf = 'pdf';
    case Word = 'docx';
    case Csv = 'csv';

    public function label(): string
    {
        return match ($this) {
            self::Excel => 'Excel',
            self::Pdf => 'PDF',
            self::Word => 'Word',
            self::Csv => 'CSV',
        };
    }

    /**
     * A short line telling people when to pick this format.
     */
    public function hint(): string
    {
        return match ($this) {
            self::Excel => 'Spreadsheet to sort, filter and total',
            self::Pdf => 'Ready to print, sign or attach',
            self::Word => 'Document to edit before sending',
            self::Csv => 'Plain data for other systems',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::Excel => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Pdf => 'application/pdf',
            self::Word => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            self::Csv => 'text/csv; charset=UTF-8',
        };
    }
}

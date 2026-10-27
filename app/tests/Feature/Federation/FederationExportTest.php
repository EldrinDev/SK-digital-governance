<?php

namespace Tests\Feature\Federation;

use App\Models\AccomplishmentReport;
use App\Models\Barangay;
use App\Models\Ppa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ZipArchive;

class FederationExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function formats(): array
    {
        return ['excel' => ['xlsx'], 'pdf' => ['pdf'], 'word' => ['docx'], 'csv' => ['csv']];
    }

    #[DataProvider('formats')]
    public function test_compliance_table_downloads_in_every_format(string $format): void
    {
        $ppa = Ppa::factory()->create();

        $response = $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.compliance.export', ['format' => $format]))
            ->assertOk()
            ->assertDownload('compliance-'.now()->year.'.'.$format);

        $this->assertStringContainsString($format === 'pdf' ? 'Barangay compliance, '.now()->year : $ppa->barangay->name, $this->text($response, $format));
    }

    #[DataProvider('formats')]
    public function test_barangay_export_lists_its_ppas_in_every_format(string $format): void
    {
        $ppa = Ppa::factory()->create(['title' => 'Coastal clean-up drive', 'budget' => 15000]);

        $response = $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.barangays.export', [$ppa->barangay, 'format' => $format]))
            ->assertOk()
            ->assertDownload();

        $this->assertStringContainsString($format === 'pdf' ? 'SK '.$ppa->barangay->name : 'Coastal clean-up drive', $this->text($response, $format));
    }

    #[DataProvider('formats')]
    public function test_accomplishment_report_downloads_in_every_format(string $format): void
    {
        $ppa = Ppa::factory()->create(['title' => 'Coastal clean-up drive']);
        $report = AccomplishmentReport::factory()->create(['abyip_id' => $ppa->abyip_id]);

        $response = $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.reports.export', [$report, 'format' => $format]))
            ->assertOk()
            ->assertDownload();

        $this->assertStringContainsString($format === 'pdf' ? 'ABYIP '.$report->snapshot['year'] : 'Coastal clean-up drive', $this->text($response, $format));
    }

    public function test_spreadsheet_keeps_money_as_numbers_and_text_as_text(): void
    {
        $ppa = Ppa::factory()->create(['title' => '=HYPERLINK("http://evil.test")', 'budget' => 15000]);

        $response = $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.barangays.export', [$ppa->barangay, 'format' => 'xlsx']));

        $rows = $this->sheets($response)['ABYIP '.$ppa->abyip->year.' PPAs'];
        $ppaRow = collect($rows)->first(fn (array $row): bool => $row[0] === '=HYPERLINK("http://evil.test")');

        $this->assertNotNull($ppaRow, 'The title is stored as text, not run as a formula.');
        $this->assertSame(15000.0, (float) $ppaRow[6]);
    }

    public function test_officers_cannot_export_federation_reports(): void
    {
        $officer = User::factory()->officer()->create();
        $report = AccomplishmentReport::factory()->create();

        $this->actingAs($officer)->get(route('federation.barangays.export', [$report->barangay, 'format' => 'pdf']))->assertForbidden();
        $this->actingAs($officer)->get(route('federation.reports.export', [$report, 'format' => 'pdf']))->assertForbidden();
    }

    public function test_non_participating_barangay_cannot_be_exported(): void
    {
        $barangay = Barangay::factory()->create(['is_participating' => false]);

        $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.barangays.export', [$barangay, 'format' => 'xlsx']))
            ->assertNotFound();
    }

    public function test_unknown_format_is_rejected(): void
    {
        $this->actingAs(User::factory()->federation()->create())
            ->get(route('federation.compliance.export', ['format' => 'exe']))
            ->assertSessionHasErrors('format');
    }

    /**
     * The readable text inside a downloaded file, whatever its format.
     */
    private function text(TestResponse $response, string $format): string
    {
        return match ($format) {
            'csv' => $response->streamedContent(),
            'pdf' => $this->pdfText($response->getContent()),
            'xlsx' => collect($this->sheets($response))->flatten()->implode(' '),
            'docx' => strip_tags($this->zipEntry($response->streamedContent(), 'word/document.xml')),
        };
    }

    /**
     * The PDF's title. Dompdf embeds a subset font, so the page text itself can't be read back.
     */
    private function pdfText(string $pdf): string
    {
        $this->assertStringStartsWith('%PDF', $pdf);
        preg_match('/\/Title\s*(?:\((.*?)\)|<([0-9A-Fa-f]+)>)/s', $pdf, $match);

        $title = ($match[2] ?? '') !== '' ? hex2bin($match[2]) : stripcslashes($match[1] ?? '');

        return str_starts_with($title, "\xFE\xFF") ? mb_convert_encoding(substr($title, 2), 'UTF-8', 'UTF-16BE') : $title;
    }

    /**
     * Each worksheet's cell values, keyed by sheet name.
     *
     * @return array<string, list<list<mixed>>>
     */
    private function sheets(TestResponse $response): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->streamedContent());
        $spreadsheet = IOFactory::load($path);
        @unlink($path);

        $sheets = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheets[$sheet->getTitle()] = $sheet->toArray(null, false, false);
        }

        $spreadsheet->disconnectWorksheets();

        return $sheets;
    }

    private function zipEntry(string $contents, string $entry): string
    {
        $path = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($path, $contents);
        $zip = new ZipArchive;
        $zip->open($path);
        $xml = (string) $zip->getFromName($entry);
        $zip->close();
        @unlink($path);

        return $xml;
    }
}

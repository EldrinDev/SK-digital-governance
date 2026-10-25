<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Evidence;
use App\Models\Ppa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceControllerTest extends TestCase
{
    use RefreshDatabase;

    private Ppa $ppa;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        $this->ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->approved()]);
        $this->officer = User::factory()->officer()->create(['barangay_id' => $this->ppa->barangay_id]);
    }

    public function test_officer_uploads_internal_evidence_by_default(): void
    {
        $this->actingAs($this->officer)->post(route('officer.ppas.evidence.store', $this->ppa), [
            'title' => 'Day 1 attendance sheet',
            'file' => UploadedFile::fake()->image('day1.jpg'),
        ])->assertRedirect(route('officer.ppas.show', $this->ppa));

        $evidence = $this->ppa->evidence()->sole();
        $this->assertFalse($evidence->is_public);
        $this->assertSame('day1.jpg', $evidence->original_name);
        $this->assertSame($this->officer->id, $evidence->uploaded_by);
        Storage::assertExists($evidence->path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'evidence.uploaded', 'subject_id' => $this->ppa->id]);
    }

    public function test_rejects_other_file_types(): void
    {
        $this->actingAs($this->officer)->post(route('officer.ppas.evidence.store', $this->ppa), [
            'title' => 'Spreadsheet',
            'file' => UploadedFile::fake()->create('budget.xlsx', 20),
        ])->assertSessionHasErrors(['file' => 'Upload a photo (JPG, PNG, WebP) or a PDF.']);
    }

    public function test_upload_needs_an_approved_abyip(): void
    {
        $draftPpa = Ppa::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $draftPpa->barangay_id]);

        $this->actingAs($officer)->post(route('officer.ppas.evidence.store', $draftPpa), [
            'title' => 'Too early',
            'file' => UploadedFile::fake()->image('x.jpg'),
        ])->assertForbidden();
    }

    public function test_officer_toggles_visibility_and_deletes(): void
    {
        $evidence = Evidence::factory()->for($this->ppa)->create();
        Storage::put($evidence->path, 'image');

        $this->actingAs($this->officer)->patch(route('officer.ppas.evidence.update', [$this->ppa, $evidence]), ['is_public' => '1']);
        $this->assertTrue($evidence->fresh()->is_public);

        $this->actingAs($this->officer)->delete(route('officer.ppas.evidence.destroy', [$this->ppa, $evidence]));
        $this->assertModelMissing($evidence);
        Storage::assertMissing($evidence->path);
    }

    public function test_officer_of_another_barangay_cannot_open_the_file(): void
    {
        $evidence = Evidence::factory()->for($this->ppa)->create();
        Storage::put($evidence->path, 'image');

        $this->actingAs(User::factory()->officer()->create())
            ->get(route('officer.ppas.evidence.show', [$this->ppa, $evidence]))
            ->assertNotFound();

        $this->actingAs($this->officer)
            ->get(route('officer.ppas.evidence.show', [$this->ppa, $evidence]))
            ->assertOk();
    }

    public function test_evidence_of_another_ppa_is_not_found_through_this_one(): void
    {
        $other = Evidence::factory()->create();

        $this->actingAs($this->officer)
            ->delete(route('officer.ppas.evidence.destroy', [$this->ppa, $other]))
            ->assertNotFound();
    }
}

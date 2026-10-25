<?php

namespace Tests\Feature\Officer;

use App\Models\Meeting;
use App\Models\Resolution;
use App\Models\User;
use App\ResolutionOutcome;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResolutionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = User::factory()->officer()->create();
    }

    public function test_officer_adds_resolution_from_a_session_with_signed_copy(): void
    {
        Storage::fake();
        $meeting = Meeting::factory()->for($this->officer->barangay)->create();

        $this->actingAs($this->officer)->post(route('officer.resolutions.store'), [
            'meeting_id' => $meeting->id,
            'number' => 'Res. No. 12, s. 2026',
            'title' => 'Approving the 2027 ABYIP',
            'outcome' => 'approved',
            'adopted_on' => now()->toDateString(),
            'file' => UploadedFile::fake()->create('res.pdf', 50, 'application/pdf'),
        ])->assertRedirect(route('officer.meetings.show', $meeting));

        $resolution = Resolution::sole();
        $this->assertSame(ResolutionOutcome::Approved, $resolution->outcome);
        $this->assertSame($this->officer->barangay_id, $resolution->barangay_id);
        Storage::assertExists($resolution->file_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'resolution.recorded']);
    }

    public function test_duplicate_number_in_the_same_barangay_is_rejected(): void
    {
        Resolution::factory()->for($this->officer->barangay)->create(['number' => 'Res. No. 1']);

        $this->actingAs($this->officer)->post(route('officer.resolutions.store'), [
            'number' => 'Res. No. 1',
            'title' => 'Again',
            'outcome' => 'approved',
            'adopted_on' => now()->toDateString(),
        ])->assertSessionHasErrors(['number' => 'Your register already has a resolution with this number.']);
    }

    public function test_same_number_is_fine_in_another_barangay(): void
    {
        Resolution::factory()->create(['number' => 'Res. No. 1']);

        $this->actingAs($this->officer)->post(route('officer.resolutions.store'), [
            'number' => 'Res. No. 1',
            'title' => 'Our own first resolution',
            'outcome' => 'deferred',
            'adopted_on' => now()->toDateString(),
        ])->assertSessionHasNoErrors();
    }

    public function test_cannot_attach_to_another_barangays_session(): void
    {
        $this->actingAs($this->officer)->post(route('officer.resolutions.store'), [
            'meeting_id' => Meeting::factory()->create()->id,
            'number' => 'Res. No. 5',
            'title' => 'Sneaky',
            'outcome' => 'approved',
            'adopted_on' => now()->toDateString(),
        ])->assertSessionHasErrors('meeting_id');
    }

    public function test_register_lists_only_own_resolutions_and_searches(): void
    {
        Resolution::factory()->for($this->officer->barangay)->create(['title' => 'Coastal clean-up funding']);
        Resolution::factory()->for($this->officer->barangay)->create(['title' => 'Sports fest venue']);
        Resolution::factory()->create(['title' => 'Elsewhere resolution']);

        $this->actingAs($this->officer)
            ->get(route('officer.resolutions.index', ['search' => 'coastal']))
            ->assertSeeText('Coastal clean-up funding')
            ->assertDontSeeText('Sports fest venue')
            ->assertDontSeeText('Elsewhere resolution');
    }
}

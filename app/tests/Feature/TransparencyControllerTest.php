<?php

namespace Tests\Feature;

use App\Models\Abyip;
use App\Models\Barangay;
use App\Models\Evidence;
use App\Models\Ppa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransparencyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_board_lists_participating_barangays_for_guests(): void
    {
        Barangay::factory()->create(['name' => 'Santa Rita']);
        Barangay::factory()->create(['name' => 'Asinan', 'is_participating' => false]);

        $this->get(route('transparency.index'))
            ->assertOk()
            ->assertSeeText('Santa Rita')
            ->assertDontSeeText('Asinan');
    }

    public function test_only_published_programs_appear(): void
    {
        $published = Abyip::factory()->approved()->create(['published_at' => now()]);
        Ppa::factory()->create(['abyip_id' => $published->id, 'title' => 'Published summit']);

        $unpublished = Abyip::factory()->approved()->for($published->cbydp)->create(['barangay_id' => $published->barangay_id, 'year' => $published->year + 1]);
        Ppa::factory()->create(['abyip_id' => $unpublished->id, 'title' => 'Hidden clean-up']);

        $this->get(route('transparency.show', $published->barangay))
            ->assertOk()
            ->assertSeeText('Published summit')
            ->assertDontSeeText('Hidden clean-up');
    }

    public function test_non_participating_barangay_is_not_found(): void
    {
        $this->get(route('transparency.show', Barangay::factory()->create(['is_participating' => false])))->assertNotFound();
    }

    public function test_public_evidence_of_published_program_is_served(): void
    {
        Storage::fake();
        $ppa = Ppa::factory()->create(['abyip_id' => Abyip::factory()->approved()->create(['published_at' => now()])]);
        $public = Evidence::factory()->public()->for($ppa)->create(['title' => 'Clean-up photo']);
        $internal = Evidence::factory()->for($ppa)->create(['title' => 'Attendance sheet with names']);
        Storage::put($public->path, 'img');
        Storage::put($internal->path, 'img');

        $this->get(route('transparency.show', $ppa->barangay))
            ->assertSee('Clean-up photo')
            ->assertDontSee('Attendance sheet with names');

        $this->get(route('transparency.evidence', $public))->assertOk();
        $this->get(route('transparency.evidence', $internal))->assertNotFound();
    }

    public function test_public_evidence_of_a_barangay_that_was_turned_off_is_not_served(): void
    {
        Storage::fake();
        $evidence = Evidence::factory()->public()->for(Ppa::factory()->state([
            'abyip_id' => Abyip::factory()->approved()->state(['published_at' => now()]),
        ]))->create();
        Storage::put($evidence->path, 'img');

        $this->get(route('transparency.evidence', $evidence))->assertOk();

        $evidence->ppa->barangay->update(['is_participating' => false]);

        $this->get(route('transparency.evidence', $evidence))->assertNotFound();
    }

    public function test_public_evidence_of_unpublished_program_is_not_served(): void
    {
        Storage::fake();
        $evidence = Evidence::factory()->public()->create();
        Storage::put($evidence->path, 'img');

        $this->get(route('transparency.evidence', $evidence))->assertNotFound();
    }
}

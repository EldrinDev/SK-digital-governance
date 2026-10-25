<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbyipPublicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_publishes_and_unpublishes_an_approved_abyip(): void
    {
        $abyip = Abyip::factory()->approved()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $abyip->barangay_id]);

        $this->actingAs($officer)->post(route('officer.abyips.publication.store', $abyip))->assertRedirect(route('officer.abyips.show', $abyip));
        $this->assertTrue($abyip->fresh()->isPublished());
        $this->assertDatabaseHas('audit_logs', ['action' => 'abyip.published', 'subject_id' => $abyip->id]);

        $this->actingAs($officer)->delete(route('officer.abyips.publication.destroy', $abyip));
        $this->assertFalse($abyip->fresh()->isPublished());
        $this->assertDatabaseHas('audit_logs', ['action' => 'abyip.unpublished', 'subject_id' => $abyip->id]);
    }

    public function test_draft_abyip_cannot_be_published(): void
    {
        $abyip = Abyip::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $abyip->barangay_id]);

        $this->actingAs($officer)->post(route('officer.abyips.publication.store', $abyip))->assertForbidden();
        $this->assertFalse($abyip->fresh()->isPublished());
    }

    public function test_officer_of_another_barangay_gets_not_found(): void
    {
        $abyip = Abyip::factory()->approved()->create();

        $this->actingAs(User::factory()->officer()->create())
            ->post(route('officer.abyips.publication.store', $abyip))
            ->assertNotFound();
    }
}

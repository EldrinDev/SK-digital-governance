<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Evidence;
use App\Models\Ppa;
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

    public function test_publishing_asks_first_and_says_what_goes_public(): void
    {
        $abyip = Abyip::factory()->approved()->create(['year' => 2026]);
        $officer = User::factory()->officer()->create(['barangay_id' => $abyip->barangay_id]);
        $ppa = Ppa::factory()->create(['abyip_id' => $abyip->id]);
        Evidence::factory()->public()->count(2)->for($ppa)->create();
        Evidence::factory()->for($ppa)->create();

        $this->actingAs($officer)->get(route('officer.abyips.show', $abyip))
            ->assertOk()
            ->assertSee('data-confirm="publish-dialog"', false)
            ->assertSeeText('Publish ABYIP 2026 to the board?')
            ->assertSeeText('2 evidence files you marked public')
            ->assertSeeText('1 internal evidence file, and the names and contact details of the youth.');

        $abyip->forceFill(['published_at' => now()])->save();

        $this->actingAs($officer)->get(route('officer.abyips.show', $abyip))
            ->assertDontSee('publish-dialog', false);
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

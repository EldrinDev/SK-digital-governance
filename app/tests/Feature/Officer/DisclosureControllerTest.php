<?php

namespace Tests\Feature\Officer;

use App\Models\Abyip;
use App\Models\Cbydp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisclosureControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sorts_abyips_into_ready_published_and_pending(): void
    {
        $officer = User::factory()->officer()->create();
        $cbydp = Cbydp::factory()->approved()->for($officer->barangay)->create(['start_year' => 2026, 'end_year' => 2028]);
        Abyip::factory()->approved()->for($cbydp)->create(['barangay_id' => $officer->barangay_id, 'year' => 2026, 'published_at' => now()]);
        Abyip::factory()->approved()->for($cbydp)->create(['barangay_id' => $officer->barangay_id, 'year' => 2027]);
        Abyip::factory()->for($cbydp)->create(['barangay_id' => $officer->barangay_id, 'year' => 2028]);

        $response = $this->actingAs($officer)->get(route('officer.disclosure.index'))->assertOk();

        $this->assertSame([2026], $response->viewData('published')->pluck('year')->values()->all());
        $this->assertSame([2027], $response->viewData('ready')->pluck('year')->values()->all());
        $this->assertSame([2028], $response->viewData('pending')->pluck('year')->values()->all());
    }
}

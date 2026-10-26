<?php

namespace Tests\Feature\Seeders;

use App\Models\Abyip;
use App\Models\AccomplishmentReport;
use App\Models\Barangay;
use App\Models\Ppa;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    public function test_seeds_three_pilot_barangays_at_different_stages(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->assertSame(17, Barangay::participating()->count());
        $this->assertSame(0, Barangay::doesntHave('announcements')->count(), 'Every barangay has at least one post.');
        $this->assertSame(17, Barangay::whereHas('announcements', fn ($query) => $query->whereNotNull('image_credit'))->count(), 'Every barangay has a credited photo.');
        $this->assertSame(DemoDataSeeder::PILOT, Barangay::has('cbydps')->orderByRaw("case name when 'Santa Rita' then 0 when 'Barretto' then 1 else 2 end")->pluck('name')->all());

        $santaRita = Barangay::firstWhere('name', 'Santa Rita');
        $this->assertSame(1, Ppa::where('barangay_id', $santaRita->id)->delayed()->count());
        $this->assertSame(1, AccomplishmentReport::compile(Abyip::where('barangay_id', $santaRita->id)->sole())['missing_evidence']);

        $this->actingAs(User::firstWhere('email', 'federation@sk.test'))
            ->get(route('federation.dashboard'))
            ->assertOk()
            ->assertSeeText('Santa Rita')
            ->assertSeeText('Barretto')
            ->assertSeeText('Kalaklan');

        $this->actingAs(User::firstWhere('email', 'officer@sk.test'))->get(route('officer.dashboard'))->assertOk();
        $this->actingAs(User::firstWhere('email', 'youth@sk.test'))->get(route('dashboard'))->assertOk()->assertSeeText('How did it go?');
        $this->get(route('transparency.show', $santaRita))->assertOk()->assertSeeText('Coastal clean-up drive');
    }

    public function test_loads_sk_logos_dropped_into_the_logos_folder(): void
    {
        $file = database_path('seeders/images/logos/gordon-heights.png');
        $alreadyThere = is_file($file);

        if (! $alreadyThere) {
            imagepng(imagecreatetruecolor(64, 64), $file);
        }

        try {
            $this->seed(DemoDataSeeder::class);
        } finally {
            if (! $alreadyThere) {
                unlink($file);
            }
        }

        $gordonHeights = Barangay::firstWhere('name', 'Gordon Heights');
        $this->assertNotNull($gordonHeights->logo_path);
        Storage::assertExists($gordonHeights->logo_path);
        $this->assertStringContainsString('/barangays/', $gordonHeights->logoUrl());
    }

    public function test_refuses_to_run_twice(): void
    {
        $this->seed(DemoDataSeeder::class);

        $this->expectException(RuntimeException::class);

        $this->app->make(DemoDataSeeder::class)->run();
    }
}

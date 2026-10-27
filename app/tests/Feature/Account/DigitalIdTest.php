<?php

namespace Tests\Feature\Account;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_youth_profile_shows_their_numbers_and_digital_id_with_a_qr(): void
    {
        $youth = User::factory()->create(['name' => 'Yvonne Youth']);

        $this->actingAs($youth)
            ->get(route('account.edit'))
            ->assertOk()
            ->assertSeeText('Yvonne Youth')
            ->assertSeeText('Events attended')
            ->assertSeeText('Katipunan ng Kabataan')
            ->assertSee('<svg', false);

        $code = $youth->fresh()->member_code;
        $this->assertMatchesRegularExpression('/^KK-[A-Z]{1,3}-[A-HJ-NP-Z2-9]{6}$/', $code);
        $this->actingAs($youth)->get(route('account.id'))->assertOk()->assertSeeText($code)->assertSeeText('Print or save as PDF');
    }

    public function test_code_stays_the_same_until_replaced_and_the_old_one_stops_working(): void
    {
        $youth = User::factory()->create();
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id]);
        $old = $youth->memberCode();
        $this->assertSame($old, $youth->fresh()->memberCode());

        $this->actingAs($youth)->post(route('account.id.renew'))->assertSessionHas('status');

        $new = $youth->fresh()->member_code;
        $this->assertNotSame($old, $new);
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.id_renewed', 'user_id' => $youth->id]);
        $this->actingAs($officer)->get(route('youth-id.verify', $old))->assertOk()->assertSeeText('This ID isn’t valid');
        $this->actingAs($officer)->get(route('youth-id.verify', $new))->assertOk()->assertSeeText('Valid youth member ID');
    }

    public function test_officer_scans_the_id_and_checks_the_youth_in_to_todays_event(): void
    {
        $youth = User::factory()->create(['name' => 'Carlo Cruz']);
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id]);
        $event = Event::factory()->for($youth->barangay)->create(['title' => 'Liga opening', 'starts_at' => now()->addHour()]);
        $registration = $event->registrations()->create(['user_id' => $youth->id]);

        $this->actingAs($officer)
            ->get(route('youth-id.verify', $youth->memberCode()))
            ->assertOk()
            ->assertSeeText('Carlo Cruz')
            ->assertSeeText('Liga opening')
            ->assertSee(route('officer.events.registrations.update', [$event, $registration]), false);

        $this->assertDatabaseHas('audit_logs', ['action' => 'youth_id.verified', 'user_id' => $officer->id]);

        $this->actingAs($officer)
            ->from(route('youth-id.verify', $youth->memberCode()))
            ->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => 1])
            ->assertRedirect(route('youth-id.verify', $youth->memberCode()));

        $this->assertTrue($registration->fresh()->attended());
    }

    public function test_only_staff_can_open_a_scanned_id_and_other_barangays_cannot_check_in(): void
    {
        $youth = User::factory()->create(['name' => 'Private Person']);
        $code = $youth->memberCode();

        $this->get(route('youth-id.verify', $code))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['barangay_id' => $youth->barangay_id]))->get(route('youth-id.verify', $code))->assertForbidden();

        $this->actingAs(User::factory()->officer()->create())
            ->get(route('youth-id.verify', $code))
            ->assertOk()
            ->assertSeeText('Private Person')
            ->assertDontSeeText('Check in to today’s event');
    }

    public function test_staff_have_no_digital_id(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)->get(route('account.id'))->assertForbidden();
        $this->actingAs($officer)->get(route('account.edit'))->assertOk()->assertDontSeeText('Katipunan ng Kabataan');
        $this->assertNull($officer->fresh()->member_code);
    }
}

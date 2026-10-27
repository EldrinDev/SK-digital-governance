<?php

namespace Tests\Feature\Account;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_can_open_account_settings(): void
    {
        foreach ([User::factory()->create(), User::factory()->officer()->create(), User::factory()->federation()->create(), User::factory()->administrator()->create()] as $user) {
            $this->actingAs($user)->get(route('account.edit'))->assertOk()->assertSee($user->email);
        }
    }

    public function test_user_updates_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.update'), ['name' => 'Juana Dela Cruz', 'email' => 'juana@example.com'])
            ->assertSessionHasErrors(['email_password' => 'Enter your password to change your email.']);

        $this->actingAs($user)
            ->patch(route('account.update'), ['name' => 'Juana Dela Cruz', 'email' => 'juana@example.com', 'email_password' => 'password'])
            ->assertSessionHas('status', 'Your details were saved.');

        $this->assertSame('juana@example.com', $user->fresh()->email);
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.updated', 'user_id' => $user->id]);
    }

    public function test_email_must_be_unused(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs(User::factory()->create())
            ->patch(route('account.update'), ['name' => 'Someone', 'email' => 'taken@example.com'])
            ->assertSessionHasErrors(['email' => 'Another account already uses this email.']);
    }

    public function test_staff_cannot_change_their_own_email(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)
            ->patch(route('account.update'), ['name' => 'Renamed Officer', 'email' => 'new@example.com', 'email_password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Staff emails are managed by an administrator. Ask one to change it.']);

        $this->actingAs($officer)
            ->patch(route('account.update'), ['name' => 'Renamed Officer', 'email' => $officer->email])
            ->assertSessionHasNoErrors();

        $this->assertSame('Renamed Officer', $officer->fresh()->name);
    }

    public function test_password_change_needs_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('account.password.update'), ['current_password' => 'wrong', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasErrors(['current_password' => 'That isn’t your current password.']);

        $this->actingAs($user)
            ->put(route('account.password.update'), ['current_password' => 'password', 'password' => 'new-password-1', 'password_confirmation' => 'new-password-1'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'account.password_changed', 'user_id' => $user->id]);
    }

    public function test_youth_downloads_own_data(): void
    {
        $youth = User::factory()->create(['name' => 'Data Owner']);
        $event = Event::factory()->for($youth->barangay)->create(['title' => 'Coastal cleanup']);
        $event->registrations()->create(['user_id' => $youth->id]);

        $this->actingAs($youth)
            ->get(route('account.data'))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="my-sk-data.json"')
            ->assertJsonPath('account.name', 'Data Owner')
            ->assertJsonPath('event_registrations.0.event', 'Coastal cleanup');
    }

    public function test_youth_closes_account_and_personal_details_are_erased(): void
    {
        $youth = User::factory()->create(['name' => 'Leaving Youth']);

        $this->actingAs($youth)
            ->delete(route('account.destroy'), ['confirm_password' => 'wrong'])
            ->assertSessionHasErrors('confirm_password');

        $this->actingAs($youth)->delete(route('account.destroy'), ['confirm_password' => 'password'])->assertRedirect(route('home'));

        $this->assertGuest();
        $youth->refresh();
        $this->assertSame('Deleted account', $youth->name);
        $this->assertStringEndsWith('@deleted.invalid', $youth->email);
        $this->assertFalse($youth->isActive());
    }

    public function test_closing_an_account_also_erases_the_name_from_older_audit_entries(): void
    {
        $youth = User::factory()->create(['name' => 'Leaving Youth']);
        $officer = User::factory()->officer()->create(['barangay_id' => $youth->barangay_id]);
        $event = Event::factory()->for($youth->barangay)->create(['starts_at' => now()->addHour()]);
        $registration = $event->registrations()->create(['user_id' => $youth->id]);
        $this->actingAs($officer)->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => 1]);
        $this->actingAs($officer)->get(route('youth-id.verify', $youth->memberCode()));
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.checked_in', 'properties->youth' => 'Leaving Youth']);

        $this->actingAs($youth)->delete(route('account.destroy'), ['confirm_password' => 'password']);

        $this->assertDatabaseMissing('audit_logs', ['properties->youth' => 'Leaving Youth']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.checked_in', 'properties->youth' => 'Deleted account']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'youth_id.verified', 'subject_id' => $youth->id, 'properties' => null]);
    }

    public function test_closing_an_account_cannot_rewrite_entries_about_someone_else_with_the_same_name(): void
    {
        $other = User::factory()->create(['name' => 'Same Name']);
        $officer = User::factory()->officer()->create(['barangay_id' => $other->barangay_id]);
        $event = Event::factory()->for($other->barangay)->create(['starts_at' => now()->addHour()]);
        $registration = $event->registrations()->create(['user_id' => $other->id]);
        $this->actingAs($officer)->patch(route('officer.events.registrations.update', [$event, $registration]), ['attended' => 1]);

        $impostor = User::factory()->create(['name' => 'Same Name']);
        $this->actingAs($impostor)->delete(route('account.destroy'), ['confirm_password' => 'password']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'event.checked_in', 'properties->youth' => 'Same Name']);
    }

    public function test_staff_cannot_close_their_own_account_or_download_youth_data(): void
    {
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)->delete(route('account.destroy'), ['confirm_password' => 'password'])->assertForbidden();
        $this->actingAs($officer)->get(route('account.data'))->assertForbidden();
    }
}

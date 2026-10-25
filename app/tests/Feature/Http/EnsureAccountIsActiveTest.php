<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureAccountIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_account_is_signed_out_on_next_request(): void
    {
        $officer = User::factory()->officer()->create(['deactivated_at' => now()]);

        $this->actingAs($officer)
            ->get(route('officer.dashboard'))
            ->assertRedirect(route('staff.login'))
            ->assertSessionHasErrors('credentials');

        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $user = User::factory()->create(['deactivated_at' => now()]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('credentials');

        $this->assertGuest();
    }
}

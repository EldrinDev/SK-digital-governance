<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_carry_security_headers(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_missing_pages_use_the_branded_error_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSeeText('Page not found')->assertSee('Go to the home page');
    }

    public function test_forbidden_page_uses_the_branded_error_page(): void
    {
        $this->actingAs(User::factory()->officer()->create())
            ->delete(route('account.destroy'), ['confirm_password' => 'password'])
            ->assertForbidden()
            ->assertSeeText('You can’t do this here');
    }

    public function test_youth_form_submissions_are_rate_limited(): void
    {
        $youth = User::factory()->create();

        foreach (range(1, 15) as $attempt) {
            $this->actingAs($youth)->post(route('feedback.store'), ['message' => "Idea {$attempt}"]);
        }

        $this->actingAs($youth)->post(route('feedback.store'), ['message' => 'One too many'])->assertTooManyRequests();
        $this->actingAs($youth)->get(route('dashboard'))->assertOk();
    }
}

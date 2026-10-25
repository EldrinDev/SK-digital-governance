<?php

namespace Tests\Feature\Officer;

use App\Models\Feedback;
use App\Models\User;
use App\Notifications\FeedbackAnswered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_replies_once_and_youth_is_emailed(): void
    {
        Notification::fake();
        $feedback = Feedback::factory()->create(['message' => 'More sports, please.']);
        $officer = User::factory()->officer()->create(['barangay_id' => $feedback->barangay_id]);

        $this->actingAs($officer)->get(route('officer.feedback.index'))->assertSeeText('More sports, please.');

        $this->actingAs($officer)->patch(route('officer.feedback.update', $feedback), ['response' => 'A league starts next month.']);

        $feedback->refresh();
        $this->assertSame('A league starts next month.', $feedback->response);
        $this->assertSame($officer->id, $feedback->responded_by);
        Notification::assertSentTo($feedback->author, FeedbackAnswered::class);

        $this->actingAs($officer)
            ->patch(route('officer.feedback.update', $feedback), ['response' => 'Changed my mind'])
            ->assertForbidden();
        $this->assertSame('A league starts next month.', $feedback->fresh()->response);
    }

    public function test_feedback_of_another_barangay_is_hidden_and_not_found(): void
    {
        $feedback = Feedback::factory()->create(['message' => 'Elsewhere message']);
        $officer = User::factory()->officer()->create();

        $this->actingAs($officer)->get(route('officer.feedback.index'))->assertDontSeeText('Elsewhere message');
        $this->actingAs($officer)->patch(route('officer.feedback.update', $feedback), ['response' => 'Hi'])->assertNotFound();
    }
}

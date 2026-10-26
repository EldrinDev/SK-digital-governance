<?php

namespace Tests\Feature\Youth;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyResponseControllerTest extends TestCase
{
    use RefreshDatabase;

    private Survey $survey;

    private User $youth;

    private SurveyQuestion $choice;

    private SurveyQuestion $rating;

    protected function setUp(): void
    {
        parent::setUp();

        $this->survey = Survey::factory()->opened()->create(['title' => 'Programs survey']);
        $this->choice = SurveyQuestion::factory()->for($this->survey)->choice(['Sports', 'Arts'])->create(['position' => 1]);
        $this->rating = SurveyQuestion::factory()->for($this->survey)->rating()->create(['position' => 2]);
        $this->youth = User::factory()->create(['barangay_id' => $this->survey->barangay_id]);
    }

    public function test_dashboard_lists_open_survey_and_youth_answers_once(): void
    {
        $this->actingAs($this->youth)->get(route('dashboard'))->assertSeeText('Programs survey');

        $this->actingAs($this->youth)
            ->post(route('surveys.responses.store', $this->survey), ['answers' => [$this->choice->id => 'Arts', $this->rating->id => 4]])
            ->assertRedirect(route('dashboard'));

        $this->assertSame(['Arts', '4'], $this->survey->responses()->sole()->answers()->orderBy('id')->pluck('value')->all());
        $this->actingAs($this->youth)->get(route('dashboard'))->assertDontSeeText('Programs survey');
        $this->actingAs($this->youth)->post(route('surveys.responses.store', $this->survey), ['answers' => [$this->choice->id => 'Sports', $this->rating->id => 1]])->assertForbidden();
    }

    public function test_invalid_choice_and_missing_answer_are_rejected(): void
    {
        $this->actingAs($this->youth)
            ->post(route('surveys.responses.store', $this->survey), ['answers' => [$this->choice->id => 'Cooking']])
            ->assertSessionHasErrors(["answers.{$this->choice->id}", "answers.{$this->rating->id}" => 'Answer this question.']);

        $this->assertSame(0, $this->survey->responses()->count());
    }

    public function test_youth_of_another_barangay_cannot_see_it(): void
    {
        $this->actingAs(User::factory()->create())->get(route('surveys.answer', $this->survey))->assertNotFound();
    }

    public function test_closed_survey_takes_no_answers(): void
    {
        $this->survey->forceFill(['closed_at' => now()])->save();

        $this->actingAs($this->youth)->get(route('surveys.answer', $this->survey))->assertForbidden();
    }
}

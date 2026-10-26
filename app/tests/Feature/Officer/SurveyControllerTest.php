<?php

namespace Tests\Feature\Officer;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use App\QuestionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = User::factory()->officer()->create();
    }

    public function test_officer_builds_and_opens_a_survey(): void
    {
        $this->actingAs($this->officer)->post(route('officer.surveys.store'), ['title' => 'Next year’s programs']);
        $survey = Survey::sole();
        $this->assertSame($this->officer->barangay_id, $survey->barangay_id);

        $this->actingAs($this->officer)->post(route('officer.surveys.questions.store', $survey), [
            'prompt' => 'Which sport?',
            'type' => 'choice',
            'options' => "Basketball\nVolleyball\n\nBasketball",
        ]);
        $this->assertSame(['Basketball', 'Volleyball'], $survey->questions()->sole()->options);

        $this->actingAs($this->officer)->patch(route('officer.surveys.status.update', $survey), ['action' => 'open']);
        $this->assertTrue($survey->fresh()->isOpen());
    }

    public function test_choice_question_needs_two_choices(): void
    {
        $survey = Survey::factory()->for($this->officer->barangay)->create();

        $this->actingAs($this->officer)
            ->post(route('officer.surveys.questions.store', $survey), ['prompt' => 'Pick', 'type' => 'choice', 'options' => 'Only one'])
            ->assertSessionHasErrors(['options' => 'Give at least two different choices.']);
    }

    public function test_empty_survey_cannot_open(): void
    {
        $survey = Survey::factory()->for($this->officer->barangay)->create();

        $this->actingAs($this->officer)
            ->patch(route('officer.surveys.status.update', $survey), ['action' => 'open'])
            ->assertSessionHasErrors(['survey' => 'Add at least one question before opening the survey.']);
    }

    public function test_questions_are_locked_once_open(): void
    {
        $survey = Survey::factory()->opened()->for($this->officer->barangay)->create();
        $question = SurveyQuestion::factory()->for($survey)->create();

        $this->actingAs($this->officer)->post(route('officer.surveys.questions.store', $survey), ['prompt' => 'Late', 'type' => 'text'])->assertForbidden();
        $this->actingAs($this->officer)->delete(route('officer.surveys.questions.destroy', [$survey, $question]))->assertForbidden();
    }

    public function test_results_show_counts_without_names(): void
    {
        $survey = Survey::factory()->opened()->for($this->officer->barangay)->create();
        $question = SurveyQuestion::factory()->for($survey)->choice(['Basketball', 'Volleyball'])->create();
        $youth = User::factory()->create(['barangay_id' => $this->officer->barangay_id, 'name' => 'Secret Respondent']);
        $survey->responses()->create(['user_id' => $youth->id])->answers()->create(['survey_question_id' => $question->id, 'value' => 'Volleyball']);

        $this->actingAs($this->officer)
            ->get(route('officer.surveys.show', $survey))
            ->assertOk()
            ->assertSeeText('1 (100%)')
            ->assertDontSeeText('Secret Respondent');
    }

    public function test_survey_of_another_barangay_is_not_found(): void
    {
        $this->actingAs($this->officer)->get(route('officer.surveys.show', Survey::factory()->create()))->assertNotFound();
    }

    public function test_rating_summary_averages(): void
    {
        $question = SurveyQuestion::factory()->rating()->create();
        $response = $question->survey->responses()->create(['user_id' => User::factory()->create()->id]);
        $response->answers()->create(['survey_question_id' => $question->id, 'value' => '4']);
        $other = $question->survey->responses()->create(['user_id' => User::factory()->create()->id]);
        $other->answers()->create(['survey_question_id' => $question->id, 'value' => '5']);

        $this->assertSame(QuestionType::Rating, $question->type);
        $this->assertSame(4.5, $question->load('answers')->summary()['average']);
    }
}

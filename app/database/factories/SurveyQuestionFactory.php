<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\QuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'position' => 1,
            'prompt' => fake()->sentence(6).'?',
            'type' => QuestionType::Text,
        ];
    }

    /**
     * A multiple-choice question.
     *
     * @param  list<string>  $options
     */
    public function choice(array $options = ['Yes', 'No']): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::Choice,
            'options' => $options,
        ]);
    }

    /**
     * A 1-to-5 rating question.
     */
    public function rating(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => QuestionType::Rating,
        ]);
    }
}

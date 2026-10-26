<?php

namespace App\Models;

use App\QuestionType;
use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['position', 'prompt', 'type', 'options'])]
class SurveyQuestion extends Model
{
    /** @use HasFactory<SurveyQuestionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'options' => 'array',
        ];
    }

    /**
     * Summarise the answers: counts per choice or rating, or the written answers.
     *
     * @return array{type: string, total: int, counts?: array<string, int>, average?: float|null, texts?: list<string>}
     */
    public function summary(): array
    {
        $values = $this->answers->pluck('value');

        return match ($this->type) {
            QuestionType::Choice => [
                'type' => 'choice',
                'total' => $values->count(),
                'counts' => collect($this->options)->mapWithKeys(fn (string $option): array => [$option => $values->filter(fn ($value) => $value === $option)->count()])->all(),
            ],
            QuestionType::Rating => [
                'type' => 'rating',
                'total' => $values->count(),
                'counts' => collect(range(1, 5))->mapWithKeys(fn (int $score): array => [(string) $score => $values->filter(fn ($value) => (int) $value === $score)->count()])->all(),
                'average' => $values->isEmpty() ? null : round($values->avg(fn ($value) => (int) $value), 1),
            ],
            QuestionType::Text => [
                'type' => 'text',
                'total' => $values->count(),
                'texts' => $values->shuffle()->values()->all(),
            ],
        };
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * @return HasMany<SurveyAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}

<?php

namespace App;

enum QuestionType: string
{
    case Text = 'text';
    case Choice = 'choice';
    case Rating = 'rating';

    /**
     * Get the label shown to people.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Written answer',
            self::Choice => 'Multiple choice',
            self::Rating => 'Rating from 1 to 5',
        };
    }
}

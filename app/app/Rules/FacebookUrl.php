<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Accepts only https links to Facebook, so a link shown as "on Facebook" can't point anywhere else.
 */
class FacebookUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = is_string($value) ? parse_url($value) : false;
        $host = strtolower($parts['host'] ?? '');

        $isFacebook = in_array($host, ['facebook.com', 'www.facebook.com', 'm.facebook.com', 'web.facebook.com', 'fb.com', 'www.fb.com'], true);

        if ($parts === false || ($parts['scheme'] ?? '') !== 'https' || ! $isFacebook || isset($parts['user']) || isset($parts['pass'])) {
            $fail('Paste a Facebook link that starts with https://www.facebook.com/.');
        }
    }
}

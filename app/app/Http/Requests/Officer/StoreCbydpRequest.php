<?php

namespace App\Http\Requests\Officer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCbydpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isOfficer() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_year' => ['required', 'integer', 'between:2020,2100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_year.required' => 'Enter the first year the plan covers.',
            'start_year.between' => 'Enter a year between 2020 and 2100.',
        ];
    }

    /**
     * A barangay can only have one CBYDP for any given year.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('start_year')) {
                    return;
                }

                $start = $this->integer('start_year');

                $overlaps = $this->user()->barangay->cbydps()
                    ->where('start_year', '<=', $start + 2)
                    ->where('end_year', '>=', $start)
                    ->exists();

                if ($overlaps) {
                    $validator->errors()->add('start_year', 'Your barangay already has a CBYDP covering some of these years.');
                }
            },
        ];
    }
}

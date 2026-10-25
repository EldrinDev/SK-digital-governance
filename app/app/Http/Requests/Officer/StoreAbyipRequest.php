<?php

namespace App\Http\Requests\Officer;

use App\Models\Cbydp;
use App\PlanStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAbyipRequest extends FormRequest
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
            'cbydp_id' => [
                'required',
                Rule::exists(Cbydp::class, 'id')
                    ->where('barangay_id', $this->user()->barangay_id)
                    ->where('status', PlanStatus::Approved->value),
            ],
            'year' => ['required', 'integer', 'between:2020,2100'],
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
            'cbydp_id.required' => 'Choose the approved CBYDP this program comes from.',
            'cbydp_id.exists' => 'Choose one of your barangay’s approved CBYDPs.',
            'year.required' => 'Enter the year of the program.',
        ];
    }

    /**
     * The year must fall in the CBYDP's period, and only one ABYIP per year.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['cbydp_id', 'year'])) {
                    return;
                }

                $year = $this->integer('year');

                if (! $this->cbydp()->covers($year)) {
                    $validator->errors()->add('year', "Choose a year within the CBYDP’s period, {$this->cbydp()->period()}.");

                    return;
                }

                if ($this->user()->barangay->abyips()->where('year', $year)->exists()) {
                    $validator->errors()->add('year', "Your barangay already has an ABYIP for {$year}.");
                }
            },
        ];
    }

    /**
     * Get the CBYDP chosen in the form.
     */
    public function cbydp(): Cbydp
    {
        return Cbydp::findOrFail($this->integer('cbydp_id'));
    }
}

<?php

namespace App\Http\Requests\Officer;

use App\Models\Abyip;
use App\Models\CbydpPriority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Adds a PPA to a draft ABYIP, or changes one.
 */
class PpaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('ppa') ?? $this->route('abyip'));

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $year = $this->abyip()->year;

        return [
            'cbydp_priority_id' => ['required', Rule::exists(CbydpPriority::class, 'id')->where('cbydp_id', $this->abyip()->cbydp_id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target' => ['required', 'string', 'max:255'],
            'budget' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'starts_on' => ['required', 'date', "after_or_equal:{$year}-01-01", "before_or_equal:{$year}-12-31"],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on', "before_or_equal:{$year}-12-31"],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $year = $this->abyip()->year;

        return [
            'cbydp_priority_id.required' => 'Choose the CBYDP priority this PPA serves.',
            'cbydp_priority_id.exists' => 'Choose a priority from this ABYIP’s CBYDP.',
            'title.required' => 'Give the PPA a name.',
            'target.required' => 'Say what the PPA should achieve, e.g. “150 youth trained”.',
            'budget.required' => 'Enter the budget in pesos.',
            'budget.decimal' => 'Use at most two decimal places for centavos.',
            'starts_on.after_or_equal' => "The PPA must start in {$year}.",
            'starts_on.before_or_equal' => "The PPA must start in {$year}.",
            'ends_on.after_or_equal' => 'The end date can’t be before the start date.',
            'ends_on.before_or_equal' => "The PPA must end by December 31, {$year}.",
        ];
    }

    /**
     * Get the ABYIP the PPA belongs to.
     */
    public function abyip(): Abyip
    {
        return $this->route('ppa')?->abyip ?? $this->route('abyip');
    }
}

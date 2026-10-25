<?php

namespace App\Http\Requests\Officer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

/**
 * Records the SK council's approval of a CBYDP or ABYIP, with its resolution.
 */
class RecordApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('approve', $this->plan());

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resolution_number' => ['required', 'string', 'max:100'],
            'approved_on' => ['required', 'date', 'before_or_equal:today'],
            'resolution_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
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
            'resolution_number.required' => 'Enter the SK resolution number.',
            'approved_on.required' => 'Enter the date the council approved it.',
            'approved_on.before_or_equal' => 'The approval date can’t be in the future.',
            'resolution_file.required' => 'Attach a scanned copy of the signed resolution.',
            'resolution_file.mimes' => 'Attach the resolution as a PDF.',
            'resolution_file.max' => 'The PDF must be 10 MB or smaller.',
        ];
    }

    /**
     * A plan with nothing in it can't be approved.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $plan = $this->plan();
                $isEmpty = method_exists($plan, 'priorities')
                    ? $plan->priorities()->doesntExist()
                    : $plan->ppas()->doesntExist();

                if ($isEmpty) {
                    $validator->errors()->add('plan', method_exists($plan, 'priorities')
                        ? 'Add at least one priority before recording the approval.'
                        : 'Add at least one PPA before recording the approval.');
                }
            },
        ];
    }

    /**
     * Get the plan being approved: the CBYDP or ABYIP in the route.
     */
    public function plan(): Model
    {
        return $this->route('cbydp') ?? $this->route('abyip');
    }
}

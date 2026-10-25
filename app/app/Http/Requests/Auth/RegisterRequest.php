<?php

namespace App\Http\Requests\Auth;

use App\Models\Barangay;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'barangay_id' => ['required', 'integer', Rule::exists(Barangay::class, 'id')],
            'privacy_consent' => ['accepted'],
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
            'email.unique' => 'An account with this email already exists. Log in instead.',
            'password.confirmed' => 'The two passwords don’t match.',
            'barangay_id.required' => 'Choose the barangay where you live.',
            'barangay_id.exists' => 'Choose a barangay from the list.',
            'privacy_consent.accepted' => 'Read and accept the privacy notice to create an account.',
        ];
    }
}

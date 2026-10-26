<?php

namespace App\Http\Requests\Admin;

use App\Models\Barangay;
use App\Models\User;
use App\OfficerPosition;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Administrator) ?? false;
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
            'role' => ['required', Rule::enum(UserRole::class)],
            'barangay_id' => [
                Rule::requiredIf(fn (): bool => $this->selectedRole()?->belongsToBarangay() ?? false),
                'nullable',
                'integer',
                Rule::exists(Barangay::class, 'id'),
            ],
            'position' => [
                Rule::requiredIf(fn (): bool => $this->selectedRole() === UserRole::Officer),
                'nullable',
                Rule::enum(OfficerPosition::class),
            ],
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
            'email.unique' => 'Another account already uses this email.',
            'barangay_id.required' => 'Youth and barangay SK officers need a barangay.',
            'position.required' => 'Choose the officer’s position on the council.',
        ];
    }

    /**
     * Get the role chosen in the form, if it is a valid one.
     */
    public function selectedRole(): ?UserRole
    {
        return UserRole::tryFrom($this->string('role')->toString());
    }

    /**
     * Get the council position to save: officers only.
     */
    public function selectedPosition(): ?OfficerPosition
    {
        return $this->selectedRole() === UserRole::Officer
            ? OfficerPosition::tryFrom($this->string('position')->toString())
            : null;
    }

    /**
     * Get the barangay to save: none for city-wide roles.
     */
    public function barangayId(): ?int
    {
        return $this->selectedRole()?->belongsToBarangay() ? $this->integer('barangay_id') : null;
    }
}

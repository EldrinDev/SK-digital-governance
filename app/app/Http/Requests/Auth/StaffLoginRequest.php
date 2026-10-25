<?php

namespace App\Http\Requests\Auth;

use App\Models\Barangay;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Login for SK staff, who also choose the workspace they are signing in to.
 */
class StaffLoginRequest extends LoginRequest
{
    /**
     * Workspace value for SK Federation users.
     */
    public const string FEDERATION_WORKSPACE = 'federation';

    /**
     * Workspace value for system administrators.
     */
    public const string ADMINISTRATION_WORKSPACE = 'administration';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'workspace' => ['required', Rule::in([
                self::FEDERATION_WORKSPACE,
                self::ADMINISTRATION_WORKSPACE,
                ...Barangay::participating()->pluck('id')->map(fn (int $id): string => (string) $id),
            ])],
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
            'workspace.required' => 'Choose the workspace you are signing in to.',
            'workspace.in' => 'Choose a workspace from the list.',
        ];
    }

    /**
     * Match the account to the chosen workspace: a barangay for SK officers,
     * or the federation or administration console.
     *
     * @return array<string, int|string>
     */
    protected function accountConditions(): array
    {
        return match ($this->string('workspace')->toString()) {
            self::FEDERATION_WORKSPACE => ['role' => UserRole::Federation->value],
            self::ADMINISTRATION_WORKSPACE => ['role' => UserRole::Administrator->value],
            default => [
                'role' => UserRole::Officer->value,
                'barangay_id' => $this->integer('workspace'),
            ],
        };
    }
}

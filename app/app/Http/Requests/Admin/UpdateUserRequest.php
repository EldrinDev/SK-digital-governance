<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends StoreUserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('user'))],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Stop administrators from removing their own access by mistake.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->route('user')->is($this->user())) {
                    return;
                }

                if ($this->selectedRole() !== UserRole::Administrator) {
                    $validator->errors()->add('role', 'You can’t change your own role. Ask another administrator.');
                }

                if (! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'You can’t deactivate your own account.');
                }
            },
        ];
    }
}

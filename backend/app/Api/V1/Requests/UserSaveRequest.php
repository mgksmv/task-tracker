<?php

namespace App\Api\V1\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
        ];

        $uniqueEmailRule = Rule::unique(User::class, 'email');

        if ($this->isMethod(self::METHOD_PUT)) {
            $user = $this->route('user');

            $rules['email'][] = $uniqueEmailRule->ignore($user->id);
            $rules['password'][] = 'nullable';
        } else {
            $rules['email'][] = $uniqueEmailRule;
            $rules['password'][] = 'required';
        }

        return $rules;
    }
}

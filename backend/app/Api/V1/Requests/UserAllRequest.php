<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAllRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sort_field' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'string', 'in:asc,desc'],
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'string'],
        ];
    }
}

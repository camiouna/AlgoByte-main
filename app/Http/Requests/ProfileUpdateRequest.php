<?php

namespace App\Http\Requests;

use App\Models\Member; // 1. Use Member instead of User
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    // app/Http/Requests/ProfileUpdateRequest.php

public function rules(): array
{
    return [
        'username' => [
            'required',
            'string',
            'lowercase',
            'max:255',
            // This checks the 'members' table for uniqueness but ignores the current user
            Rule::unique('members', 'username')->ignore($this->user()->userId, 'userId'),
        ],
        'email' => [
            'required',
            'string',
            'lowercase',
            'email',
            'max:255',
            Rule::unique('members', 'email')->ignore($this->user()->userId, 'userId'),
        ],
    ];
}
}

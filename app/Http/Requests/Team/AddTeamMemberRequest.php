<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

final class AddTeamMemberRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // For web: email-based invitation
        // For API: user_id-based direct addition
        return [
            'email' => ['required_without:user_id', 'email'],
            'user_id' => ['required_without:email', 'integer', 'exists:users,id'],
        ];
    }
}

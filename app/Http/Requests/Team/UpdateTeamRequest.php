<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTeamRequest extends FormRequest
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
        $team = $this->user()->activeTeam ?? $this->route('team');

        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['sometimes', 'string', 'max:100', 'alpha_dash', 'unique:teams,slug,' . $team?->id],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'max:512'],
            'currency' => ['sometimes', 'string'],
            'currency_symbol' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string'],
        ];
    }
}

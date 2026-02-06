<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTeamRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:teams,slug'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'max:512'],
            'currency' => ['required', 'string'],
            'currency_symbol' => ['required', 'string'],
        ];
    }
}

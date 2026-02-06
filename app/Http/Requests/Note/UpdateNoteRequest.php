<?php

namespace App\Http\Requests\Note;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNoteRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_team_note' => ['nullable', 'boolean'],
            'related' => ['nullable', 'array'],
        ];
    }
}

<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBillRequest extends FormRequest
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
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'due_date' => ['sometimes', 'date'],
            'trial_start_date' => ['nullable', 'date'],
            'trial_end_date' => ['nullable', 'date', 'after:trial_start_date'],
            'has_trial' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_period' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
            'payment_url' => ['nullable', 'string', 'url'],
            'tags' => ['nullable', 'array'],
            'status' => ['sometimes', 'string', 'in:paid,unpaid,pending,cancelled'],
        ];
    }
}

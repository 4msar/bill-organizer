<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBillRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'trial_start_date' => ['nullable', 'date'],
            'trial_end_date' => ['nullable', 'date', 'after:trial_start_date'],
            'has_trial' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_period' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
            'payment_url' => ['nullable', 'string', 'url'],
            'tags' => ['nullable', 'array'],
            'notify_me' => ['nullable', 'boolean'],
            'auto_transaction' => ['nullable', 'array'],
            'auto_transaction.is_enabled' => ['nullable', 'boolean'],
            'auto_transaction.amount' => ['exclude_unless:auto_transaction.is_enabled,true', 'numeric', 'min:0.01'],
            'auto_transaction.payment_method' => ['nullable', 'string', 'in:cash,credit_card,debit_card,bank_transfer,paypal,crypto,check,other'],
            'auto_transaction.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

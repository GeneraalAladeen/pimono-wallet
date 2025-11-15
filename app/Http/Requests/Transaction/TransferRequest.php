<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
            'receiver_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:10|max:1000000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->receiver_id == $this->user()->id) {
                $validator->errors()->add(
                    'receiver_id', 
                    'You cannot transfer money to yourself.'
                );
            }
            
            if ($this->user()->balance < ($this->amount * 1.015)) {
                $validator->errors()->add(
                    'amount', 
                    'Insufficient balance including commission fee.'
                );
            }
        });
    }

    public function messages()
    {
        return [
            'receiver_id.exists' => 'The selected recipient does not exist.',
            'amount.min' => 'The amount must be at least 10.',
            'amount.max' => 'The amount may not be greater than 1,000,000.',
        ];
    }
}

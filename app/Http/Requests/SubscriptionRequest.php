<?php

namespace App\Http\Requests;

use App\Enums\FrequencyType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'frequency' => ['required', new Enum(FrequencyType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'city.required' => 'Please enter a city name',
            'city.min' => 'City name must be at least 2 characters',
            'city.max' => 'City name cannot exceed 100 characters',
            'email.required' => 'Please enter your email address',
            'email.email' => 'Please enter a valid email address',
            'frequency.required' => 'Please select update frequency',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}

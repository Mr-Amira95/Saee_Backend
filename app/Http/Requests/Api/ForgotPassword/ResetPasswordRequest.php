<?php

namespace App\Http\Requests\Api\ForgotPassword;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reset_token' => ['required', 'string'],
            'password'    => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function (string $attribute, mixed $value, \Closure $fail) {
                    // At least one Unicode letter (supports Arabic and other scripts)
                    if (! preg_match('/\p{L}/u', $value)) {
                        $fail('The password must contain at least one letter.');
                    }
                    // At least one digit
                    if (! preg_match('/[0-9]/', $value)) {
                        $fail('The password must contain at least one number.');
                    }
                },
            ],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422));
    }
}

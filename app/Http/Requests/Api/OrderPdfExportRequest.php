<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class OrderPdfExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids');

        if (is_string($ids)) {
            $ids = array_values(array_filter(array_map('trim', explode(',', $ids)), 'strlen'));
        }

        if (is_array($ids)) {
            $this->merge(['ids' => $ids]);
        }
    }

    public function rules(): array
    {
        return [
            'ids'          => ['nullable', 'array'],
            'ids.*'        => ['integer', 'min:1'],
            'status'       => ['nullable', 'string', Rule::in([
                'pending', 'assigned', 'picked_up', 'delivered', 'rejected', 'returned', 'cancelled', 'active',
            ])],
            'payment_type' => ['nullable', 'string', 'in:cod,prepaid'],
            'from'         => ['nullable', 'date_format:Y-m-d'],
            'to'           => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function orderIds(): array
    {
        return array_map('intval', $this->validated('ids') ?? []);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => __('The given data was invalid.'),
            'errors'  => $validator->errors(),
        ], 422));
    }
}

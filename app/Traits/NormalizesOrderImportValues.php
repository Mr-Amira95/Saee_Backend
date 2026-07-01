<?php

namespace App\Traits;

trait NormalizesOrderImportValues
{
    /**
     * Arabic <-> English labels for the CSV template header row, keyed by the
     * internal field name used throughout the import/export code.
     */
    protected function orderImportHeaderLabels(): array
    {
        return [
            'client_id'                => 'معرف العميل',
            'order_description'        => 'وصف الطلب',
            'payment_type'             => 'طريقة الدفع',
            'delivery_on_customer'     => 'التوصيل على حساب العميل',
            'delivery_customer_amount' => 'مبلغ التوصيل من العميل',
            'order_price'              => 'سعر الطلب (عند الاستلام)',
            'receiver_name'            => 'اسم المستلم',
            'receiver_phone'           => 'هاتف المستلم',
            'city_id'                  => 'معرف المدينة',
            'area_id'                  => 'معرف المنطقة',
            'address_text'             => 'العنوان',
            'notes'                    => 'ملاحظات',
            'delivery_shift'           => 'فترة التوصيل',
        ];
    }

    /**
     * Translate a list of internal field names into the CSV header row for the
     * given locale, preserving column order.
     */
    protected function localizeImportHeaders(array $fields, string $locale): array
    {
        if ($locale !== 'ar') {
            return $fields;
        }

        $labels = $this->orderImportHeaderLabels();

        return array_map(fn ($field) => $labels[$field] ?? $field, $fields);
    }

    /**
     * Map an uploaded CSV header row back to internal field names, so a template
     * downloaded with Arabic headers can be re-uploaded and parsed correctly.
     */
    protected function normalizeImportHeaderRow(array $headers): array
    {
        $arabicToField = array_flip($this->orderImportHeaderLabels());

        return array_map(function ($header) use ($arabicToField) {
            $trimmed = trim((string) $header);

            return $arabicToField[$trimmed] ?? $trimmed;
        }, $headers);
    }

    /**
     * Translate Arabic yes/no tokens to their English equivalent before the
     * existing FILTER_VALIDATE_BOOLEAN-based checks run.
     */
    protected function normalizeYesNoValue($value): string
    {
        $trimmed = trim((string) $value);

        $map = [
            'نعم' => 'true',
            'لا'  => 'false',
        ];

        return $map[$trimmed] ?? $trimmed;
    }

    /**
     * Translate Arabic payment type tokens to their English equivalent before
     * the existing in_array(['cod', 'prepaid']) checks run.
     */
    protected function normalizePaymentTypeValue($value): string
    {
        $trimmed = trim((string) $value);

        $map = [
            'عند التسليم' => 'cod',
            'مدفوع'       => 'prepaid',
        ];

        return $map[$trimmed] ?? $trimmed;
    }
}

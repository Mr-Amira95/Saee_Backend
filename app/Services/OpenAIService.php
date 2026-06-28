<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAIService
{
    /**
     * Parse an image file and extract a structured list of orders using OpenAI.
     *
     * @param string $imagePath
     * @return array
     * @throws Exception
     */
    public function parseImageForOrders(string $imagePath): array
    {
        $apiKey = config('services.openai.key');
        if (empty($apiKey)) {
            throw new Exception('OpenAI API Key is not configured in services.php.');
        }

        // Standardize the model choice. If the configured model is not vision-capable or invalid, fallback to gpt-4o.
        $model = config('services.openai.model', 'gpt-4o');
        if (empty($model) || str_contains($model, 'gpt-4.1') || str_contains($model, 'gpt-5')) {
            $model = 'gpt-4o';
        }

        if (!file_exists($imagePath)) {
            throw new Exception("Image file not found at path: {$imagePath}");
        }

        // Get image mime type and encode to base64
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);
        $mimeType = mime_content_type($imagePath) ?: 'image/jpeg';

        $prompt = "This image contains a list, table, or receipt of shipping orders. " .
            "Please extract every order row and return ONLY valid JSON matching this schema:\n\n" .
            "{\n" .
            "  \"orders\": [\n" .
            "    {\n" .
            "      \"client_id_or_name\": \"string (optional: name of client or brand if visible in the document)\",\n" .
            "      \"order_description\": \"string (product description/item names, default to empty string)\",\n" .
            "      \"payment_type\": \"cod|prepaid (must be lowercase 'cod' or 'prepaid', default to 'cod' if price > 0, otherwise 'prepaid')\",\n" .
            "      \"delivery_on_customer\": \"boolean (true if customer pays delivery fee, false if client pays, default to false)\",\n" .
            "      \"delivery_customer_amount\": \"number (numeric delivery fee to collect from customer, default to 0.00)\",\n" .
            "      \"order_price\": \"number (numeric cash/COD price of goods to be collected, default to 0.00)\",\n" .
            "      \"receiver_name\": \"string (name of the customer/receiver, mandatory)\",\n" .
            "      \"receiver_phone\": \"string (phone number of the customer/receiver, mandatory)\",\n" .
            "      \"city_name\": \"string (city name in English or Arabic, e.g. Amman, Irbid, Zarqa, Aqaba)\",\n" .
            "      \"area_name\": \"string (area or neighborhood name in English or Arabic if mentioned)\",\n" .
            "      \"address_text\": \"string (full details of receiver's address, mandatory)\",\n" .
            "      \"notes\": \"string (optional delivery notes or instructions)\",\n" .
            "      \"delivery_shift\": \"doesnt_matter|before_12pm|after_12pm (must be lowercase, default to 'doesnt_matter')\"\n" .
            "    }\n" .
            "  ]\n" .
            "}\n\n" .
            "Requirements:\n" .
            "1. Extract receivers and phone numbers accurately.\n" .
            "2. If city_name or area_name are in Arabic or English, write them exactly as shown or normalize them to their common spellings.\n" .
            "3. If a value is empty or not present in the image, return null or the default value.\n" .
            "4. Do NOT invent data.";

        try {
            Log::info("Sending vision request to OpenAI using model: {$model}");
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}",
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('OpenAI vision request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                throw new Exception('OpenAI API returned an error: ' . ($response->json('error.message') ?? $response->body()));
            }

            $content = $response->json('choices.0.message.content');
            if (empty($content)) {
                throw new Exception('OpenAI returned an empty response.');
            }

            $parsed = json_decode($content, true);
            if (!is_array($parsed) || !isset($parsed['orders'])) {
                Log::warning('Unexpected OpenAI response content format', ['content' => $content]);
                throw new Exception('OpenAI response could not be parsed into the expected orders format.');
            }

            return $parsed['orders'];

        } catch (Exception $e) {
            Log::error('Error in OpenAIService', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

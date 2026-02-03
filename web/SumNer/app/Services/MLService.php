<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class MLService
{
    protected $baseUrl;

    public function __construct()
    {
        // Default to local FastAPI address
        $this->baseUrl = env('ML_SERVICE_URL', 'http://127.0.0.1:8001');
    }

    /**
     * Send text to ML service for analysis (Summarization + NER + Sentiment)
     *
     * @param string $text
     * @param string $summaryType
     * @return array
     * @throws Exception
     */
    public function analyze(string $text, string $summaryType = 'abstractive', array $params = [], ?string $url = null): array
    {
        try {
            $payload = [
                'text' => $text,
                'summary_type' => $summaryType,
                'parameters' => (object)$params,
            ];

            if ($url) {
                $payload['url'] = $url;
            }

            $response = Http::timeout(300)->post("{$this->baseUrl}/analyze", $payload);

            if ($response->failed()) {
                throw new Exception("ML Service failed: " . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            // Log error or rethrow
            throw $e;
        }
    }
}

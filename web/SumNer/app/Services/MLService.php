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
     * Send text to ML service for analysis (Summarization + NER)
     *
     * @param string $text
     * @return array
     * @throws Exception
     */
    public function analyze(string $text): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/analyze", [
                'text' => $text,
            ]);

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

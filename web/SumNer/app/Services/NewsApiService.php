<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService
{
    protected $baseUrl = 'https://newsapi.org/v2';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.news_api.key');
    }

    /**
     * Get related articles based on a query string.
     *
     * @param string $query Keywords to search for.
     * @param string|null $category Optional category filter (not used in everything endpoint usually, but good for context).
     * @param int $limit Number of articles to return.
     * @return array
     */
    public function getRelatedArticles(string $query, ?string $category = null, int $limit = 4): array
    {
        if (empty($this->apiKey)) {
            Log::warning("NewsApiService: API Key is missing.");
            return [];
        }

        if (empty($query)) {
            return [];
        }

        try {
            // "Everything" endpoint gives better related coverage than "Top Headlines"
            $url = "{$this->baseUrl}/everything";
            
            $response = Http::timeout(10)->get($url, [
                'q' => $query,
                'searchIn' => 'title,description',
                'language' => 'en',
                'sortBy' => 'relevancy',
                'pageSize' => $limit,
                'apiKey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $articles = $response->json()['articles'] ?? [];
                
                // Filter out Removed articles or articles without images (optional cleanup)
                $filtered = array_filter($articles, function($article) {
                    return $article['title'] !== '[Removed]' && !empty($article['urlToImage']);
                });

                return array_slice($filtered, 0, $limit);
            } else {
                Log::error("NewsApiService Error: " . $response->body());
                return [];
            }
        } catch (\Exception $e) {
            Log::error("NewsApiService Exception: " . $e->getMessage());
            return [];
        }
    }
}

<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\GuzzleException;

class NewsService
{
    protected $newsApiKey;
    protected $guardianApiKey;
    protected $nytApiKey;
    protected $client;

    public function __construct()
    {
        $this->newsApiKey = config('services.newsapi.key');
        $this->guardianApiKey = config('services.guardian.key');
        $this->nytApiKey = config('services.nyt.key');
        $this->client = new Client();
    }

    public function getNewsApiHeadlines($language = 'en')
    {
        try {
            $categories = ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'];
            $client = new Client();

            // Create all promises at once
            $promises = array_combine(
                $categories,
                array_map(fn($category) => $client->requestAsync('GET', 'https://newsapi.org/v2/top-headlines', [
                    'headers' => ['X-Api-Key' => $this->newsApiKey],
                    'query' => [
                        'language' => $language,
                        'category' => $category,
                        'pageSize' => 10
                    ]
                ]), $categories)
            );

            $allArticles = collect(\GuzzleHttp\Promise\Utils::unwrap($promises))
                ->flatMap(function ($response, $category) {
                    $data = json_decode($response->getBody(), true);
                    return collect($data['articles'] ?? [])
                        ->filter(
                            fn($article) =>
                            // Filter invalid articles
                            !in_array('[Removed]', [
                                $article['title'] ?? '',
                                $article['content'] ?? '',
                                $article['description'] ?? ''
                            ])
                                && ($article['url'] ?? '') !== 'https://removed.com'
                                && !empty($article['title'])
                                && !empty($article['url'])
                                && (!empty($article['description']) || !empty($article['content']))
                        )
                        ->map(fn($article) => [
                            'title' => trim($article['title']),
                            'description' => !empty($article['description']) ? trim($article['description']) : null,
                            'content' => !empty($article['content']) ? trim($article['content']) : null,
                            'url' => $article['url'],
                            'author' => !empty($article['author']) ? trim($article['author']) : null,
                            'urlToImage' => $article['urlToImage'] ?? null,
                            'category' => $category,
                            'publishedAt' => $article['publishedAt']
                        ]);
                })
                ->shuffle()
                ->values()
                ->all();

            return $allArticles;
        } catch (GuzzleException $e) {
            Log::error("News API Error: " . $e->getMessage());
            return [];
        }
    }

    public function getGuardianNews($query = null, $section = null)
    {
        try {
            $response = $this->client->request('GET', 'https://content.guardianapis.com/search', [
                'query' => array_filter([
                    'api-key' => $this->guardianApiKey,
                    'q' => $query,
                    'section' => $section,
                    'show-fields' => 'all'
                ])
            ]);

            $data = json_decode($response->getBody(), true);
            if (isset($data['response']['results'])) {
                return array_map(function ($article) {
                    return [
                        'title' => $article['webTitle'],
                        'description' => $article['fields']['trailText'] ?? null,
                        'content' => $article['fields']['bodyText'] ?? null,
                        'url' => $article['webUrl'],
                        'author' => $article['fields']['byline'] ?? null,
                        'urlToImage' => $article['fields']['thumbnail'] ?? null,
                        'category' => $article['sectionName'],
                        'publishedAt' => $article['webPublicationDate'],
                    ];
                }, $data['response']['results']);
            }

            return [];
        } catch (GuzzleException $e) {
            Log::error("Guardian API Error: " . $e->getMessage());
            return [];
        }
    }

    // public function getNYTTopStories($section = 'home')
    // {
    //     try {
    //         $maxRetries = 3;
    //         $attempt = 0;

    //         while ($attempt < $maxRetries) {
    //             try {
    //                 $response = $this->client->request(
    //                     'GET',
    //                     "https://api.nytimes.com/svc/topstories/v2/{$section}.json",
    //                     [
    //                         'query' => [
    //                             'api-key' => $this->nytApiKey
    //                         ],
    //                         'connect_timeout' => 10,
    //                         'timeout' => 30,
    //                         'verify' => true  // SSL verification
    //                     ]
    //                 );

    //                 $data = json_decode($response->getBody(), true);

    //                 if (isset($data['results'])) {
    //                     return array_map(fn($article) => [
    //                         'title' => $article['title'],
    //                         'description' => $article['abstract'],
    //                         'content' => $article['abstract'],
    //                         'url' => $article['url'],
    //                         'author' => $article['byline'],
    //                         'urlToImage' => $article['multimedia'][0]['url'] ?? null,
    //                         'category' => $article['section'],
    //                         'publishedAt' => $article['published_date'],
    //                     ], $data['results']);
    //                 }

    //                 return [];
    //             } catch (GuzzleException $e) {
    //                 $attempt++;
    //                 if ($attempt === $maxRetries) {
    //                     throw $e;
    //                 }
    //                 // Wait before retrying (exponential backoff)
    //                 sleep(pow(2, $attempt));
    //             }
    //         }
    //     } catch (Exception $e) {
    //         Log::error("Guardian API Error: " . $e->getMessage());
    //         return [];
    //     }
    // }

    public function getNYTTopStories($section = 'home')
    {
        try {
            $response = $this->client->request(
                'GET',
                "https://api.nytimes.com/svc/topstories/v2/{$section}.json",
                [
                    'query' => [
                        'api-key' => $this->nytApiKey
                    ],
                    'connect_timeout' => 10,
                    'timeout' => 30,
                    'verify' => true
                ]
            );

            $data = json_decode($response->getBody(), true);

            if (isset($data['results'])) {
                return array_map(function ($article) {
                    return [
                        'title' => $article['title'],
                        'description' => $article['abstract'],
                        'content' => $article['abstract'],
                        'url' => $article['url'],
                        'author' => $article['byline'],
                        'urlToImage' => $article['multimedia'][0]['url'] ?? null,
                        'category' => $article['section'],
                        'publishedAt' => $article['published_date'],
                    ];
                }, $data['results']);
            }

            return [];
        } catch (GuzzleException $e) {
            Log::error("NYT API Error: " . $e->getMessage());
            return [];
        }
    }
}
<?php

namespace App\Jobs;

use Exception;
use App\Models\Article;
use App\Services\NewsService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NewsService $newsService)
    {
        Log::info("Starting FetchArticlesJob");
        try {
            DB::beginTransaction();

            // Fetch from NewsAPI
            $newsApiArticles = $newsService->getNewsApiHeadlines();
            $this->processArticles($newsApiArticles, 'NewsAPI');
            Log::info("NewsAPI articles processed: " . count($newsApiArticles));

            // Fetch from Guardian
            $guardianArticles = $newsService->getGuardianNews();
            $this->processArticles($guardianArticles, 'Guardian');
            Log::info("Guardian articles processed: " . count($guardianArticles));

            // Fetch from NYT
            $nytArticles = $newsService->getNYTTopStories();
            $this->processArticles($nytArticles, 'NYT');
            Log::info("NYT articles processed: " . count($nytArticles));

            DB::commit();
            Log::info("All articles processed successfully");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error in FetchArticlesJob: " . $e->getMessage());
            throw $e;
        }
    }

    private function processArticles($articles, $source)
    {
        try {
            foreach ($articles as $article) {
                Article::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'],
                        'description' => $article['description'] ?? null,
                        'content' => $article['content'] ?? null,
                        'source' => $source,
                        'author' => $article['author'] ?? null,
                        'image_url' => $article['urlToImage'] ?? null,
                        'category' => $article['category'] ?? null,
                        'published_at' => $article['publishedAt'],
                    ]
                );
            }
        } catch (Exception $e) {
            Log::error("Error processing articles from {$source}: " . $e->getMessage());
            throw $e;
        }
    }
}

<?php

namespace App\Services;

use App\Models\Article;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsFetchService
{

    protected $newsApiOrgKey;
    protected $nyTimesKey;
    protected $guardianKey;

    protected $sources = [
        ['name' => 'newsApiOrg', 'url' => 'https://newsapi.org/v2/everything?q=nbc&apiKey='],
        ['name' => 'guardianApi', 'url' => 'https://content.guardianapis.com/search?page-size=100&api-key='],
        ['name' => 'nyTimesApi', 'url' => 'https://api.nytimes.com/svc/archive/v1/2024/1.json?api-key=']
    ];

    public function __construct()
    {
        $this->newsApiOrgKey = env('NEWS_API_ORG_API_KEY');
        $this->nyTimesKey = env('NY_TIMES_API_KEY');
        $this->guardianKey = env('GUARDIAN_API_KEY');
    }

    public function getSourcesWithKeys()
    {
        return [
            [
                'name' => 'newsApiOrg',
                'url' => $this->sources[0]['url'] . $this->newsApiOrgKey
            ],
            [
                'name' => 'guardianApi',
                'url' => $this->sources[1]['url'] . $this->guardianKey
            ],
            [
                'name' => 'nyTimesApi',
                'url' => $this->sources[2]['url'] . $this->nyTimesKey
            ]
        ];
    }


    public function fetchAndStoreArticles()
    {
        $sources = $this->getSourcesWithKeys();
        foreach ($sources as $source) {
            $this->fetchArticlesFromSource($source);
        }
    }

    protected function fetchArticlesFromSource($source)
    {
        try {
            $response = Http::timeout(60)->get($source['url']);

            if ($response->successful()) {
                $articles = $response->json();

                if ($source['name'] == 'newsApiOrg') {
                    foreach ($articles['articles'] as $article) {
                        Article::create([
                            'title' => $article['title'],
                            'url' => $article['url'],
                            'imageUrl' => $article['urlToImage'],
                            'author' => $article['author'],
                            'source' => $article['source']['name'],
                            'description' => $article['description'],
                            'category' => 'Article',
                            'published_at' => new DateTime($article['publishedAt']),
                        ]);
                    }
                } else if ($source['name'] == 'guardianApi') {
                    foreach ($articles['response']['results'] as $article) {
                        Article::create([
                            'title' => $article['webTitle'],
                            'url' => $article['webUrl'],
                            'imageUrl' => null,
                            'author' => 'The Guardian',
                            'source' => 'The Guardian',
                            'description' => null,
                            'category' => $article['sectionName'],
                            'published_at' => new DateTime($article['webPublicationDate']),
                        ]);
                    }
                } else {
                    $counter = 0;
                    foreach ($articles['response']['docs'] as $article) {
                        Article::create([
                            'title' => $article['headline']['main'],
                            'url' => $article['web_url'],
                            'imageUrl' => null,
                            'author' => $article['byline']['original'],
                            'source' => $article['source'],
                            'description' => $article['lead_paragraph'],
                            'category' => $article['news_desk'],
                            'published_at' => new DateTime($article['pub_date']),
                        ]);
                        $counter++;

                        if ($counter > 200) {
                            break;
                        }
                    }
                }
            } else {

                Log::error("Failed to fetch articles from {$source['name']}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error fetching articles from {$source['name']}: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Services\NewsFetchService;

class ArticleController extends Controller
{
    protected $newsFetchService;

    public function __construct(NewsFetchService $newsFetchService)
    {
        $this->newsFetchService = $newsFetchService;
    }

    public function fetchArticles()
    {
        $this->newsFetchService->fetchAndStoreArticles();
        return response()->json(['message' => 'Artcles fetched and stored successfully']);
    }

    public function index(Request $request)
    {

        $keyword = $request->query('keyword');
        $category = $request->query('category');
        $source = $request->query('source');
        $date = $request->query('date');
        $page = $request->query('page', 1);

        $query = Article::query();

        if ($keyword) {
            $query->where('title', 'like', '%' . $keyword . '%')
                ->orWhere('category', 'like', '%' . $keyword . '%')
                ->orWhere('source', 'like', '%' . $keyword . '%');
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($source) {
            $query->where('source', $source);
        }

        if ($date) {
            $query->whereDate('published_at', $date);
        }

        $articles = $query->whereNot('title', '[Removed]')->paginate(12, ['*'], 'page', $page);

        return response()->json($articles);
    }


    public function fetchPersonalizedFeed(Request $request)
    {
        $user = $request->user(); // Get the authenticated user
        $preferences = $user->preferences;

        $query = Article::query();

        // Filter by user preferences
        if ($preferences) {
            if ($preferences->sources) {
                $query->whereIn('source', $preferences->sources);
            }
            if ($preferences->categories) {
                $query->whereIn('category', $preferences->categories);
            }
            if ($preferences->authors) {
                $query->whereIn('author', $preferences->authors);
            }
        }

        $articles = $query->orderBy('published_at', 'desc')->paginate(12);

        return response()->json($articles);
    }
}

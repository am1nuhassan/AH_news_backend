<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Preferences;
use Illuminate\Http\Request;

class PreferencesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'sources' => 'nullable|array',
            'categories' => 'nullable|array',
            'authors' => 'nullable|array',
        ]);

        $preference = Preferences::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'sources' => $request->sources,
                'categories' => $request->categories,
                'authors' => $request->authors,
            ]
        );

        return response()->json(['message' => 'Preferences saved successfully!', 'preference' => $preference]);
    }

    public function getOptions()
    {
        // Fetch unique sources, categories, and authors from the articles table
        $sources = Article::distinct()->pluck('source');
        $categories = Article::distinct()->pluck('category');
        $authors = Article::distinct()->pluck('author');

        return response()->json([
            'sources' => $sources,
            'categories' => $categories,
            'authors' => $authors,
        ]);
    }

    public function getSavedPreferences(Request $request)
    {
        // Get the authenticated user's ID
        $userId = $request->user()->id;

        // Fetch the user's saved preferences
        $preferences = Preferences::where('user_id', $userId)->first();

        // Return the preferences as JSON
        return response()->json([
            'sources' => $preferences->sources ?? [],
            'categories' => $preferences->categories ?? [],
            'authors' => $preferences->authors ?? [],
        ]);
    }
}

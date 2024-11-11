<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Articles",
 *     description="API Endpoints for articles"
 * )
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="Get articles list with filters",
     *     description="Retrieve articles with optional filters and pagination",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search in title, description and content",
     *         required=false,
     *         @OA\Schema(type="string", example="tech")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"business", "entertainment", "general", "health", "science", "sports", "technology"},
     *             example="technology"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter by news source",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="NewsAPI"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by published date",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2024-11-11"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             minimum=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Latest Tech News"),
     *                     @OA\Property(property="description", type="string", example="Description of the article"),
     *                     @OA\Property(property="content", type="string", example="Full content of the article"),
     *                     @OA\Property(property="url", type="string", example="https://example.com/article"),
     *                     @OA\Property(property="author", type="string", example="John Doe"),
     *                     @OA\Property(property="category", type="string", example="technology"),
     *                     @OA\Property(property="source", type="string", example="newsapi"),
     *                     @OA\Property(property="published_at", type="string", format="date-time", example="2024-03-15T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function index(Request $request)
    {
        $query = Article::query();

        // Apply filters
        if ($request->has('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->keyword}%")
                  ->orWhere('description', 'like', "%{$request->keyword}%")
                  ->orWhere('content', 'like', "%{$request->keyword}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('date')) {
            $query->whereDate('published_at', $request->date);
        }

        // Sort by published date
        $query->orderBy('published_at', 'desc');

        return response()->json($query->paginate(20),200);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{article}",
     *     tags={"Articles"},
     *     summary="Get specific article",
     *     description="Retrieve a specific article by its ID",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="article",
     *         in="path",
     *         description="Article ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Latest Tech News"),
     *             @OA\Property(property="description", type="string", example="Description of the article"),
     *             @OA\Property(property="content", type="string", example="Full content of the article"),
     *             @OA\Property(property="url", type="string", example="https://example.com/article"),
     *             @OA\Property(property="author", type="string", example="John Doe"),
     *             @OA\Property(property="category", type="string", example="technology"),
     *             @OA\Property(property="source", type="string", example="newsapi"),
     *             @OA\Property(property="published_at", type="string", format="date-time", example="2024-03-15T12:00:00Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Article].")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show(Article $article)
    {
        return response()->json($article,200);
    }
}

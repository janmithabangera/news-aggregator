<?php

namespace App\Http\Controllers\API;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="User Preferences",
 *     description="API Endpoints for managing user preferences and personalized feed"
 * )
 */
class UserPreferenceController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/preferences",
     *     tags={"User Preferences"},
     *     summary="Store or update user preferences",
     *     description="Store or update user's preferred sources, categories, and authors",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sources","categories","authors"},
     *             @OA\Property(
     *                 property="sources",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"NewsAPI", "Guardian"}
     *             ),
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"technology", "business", "health"}
     *             ),
     *             @OA\Property(
     *                 property="authors",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"John Doe", "Jane Smith"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferences stored successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="preferred_sources",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"newsapi", "guardian"}
     *             ),
     *             @OA\Property(
     *                 property="preferred_categories",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"technology", "business", "health"}
     *             ),
     *             @OA\Property(
     *                 property="preferred_authors",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"John Doe", "Jane Smith"}
     *             ),
     *             @OA\Property(property="updated_at", type="string", format="date-time"),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="sources",
     *                     type="array",
     *                     @OA\Items(type="string", example="The sources field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sources' => 'required|array',
            'categories' => 'required|array',
            'authors' => 'required|array',
        ]);
        $preference = UserPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'preferred_sources' => $validated['sources'],
                'preferred_categories' => $validated['categories'],
                'preferred_authors' => $validated['authors'],
            ]
        );

        return response()->json($preference, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/feed",
     *     tags={"User Preferences"},
     *     summary="Get personalized news feed",
     *     description="Get articles based on user's preferred sources, categories, and authors",
     *     security={{"bearerAuth":{}}},
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
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="content", type="string"),
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="author", type="string"),
     *                     @OA\Property(property="category", type="string"),
     *                     @OA\Property(property="source", type="string"),
     *                     @OA\Property(property="published_at", type="string", format="date-time")
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
    public function getPersonalizedFeed()
    {
        $preferences = auth()->user()->preferences;

        $query = Article::query();

        if (!empty($preferences->preferred_authors)) {
            $query->where(function ($q) use ($preferences) {
                foreach ($preferences->preferred_authors as $author) {
                    $q->orWhere('author', 'LIKE', '%' . $author . '%');
                }
            });
        }
        if (!empty($preferences->preferred_categories)) {
            $query->whereIn('category', $preferences->preferred_categories);
        }

        if (!empty($preferences->preferred_sources)) {
            $query->whereIn('source', $preferences->preferred_sources);
        }

        return response()->json($query->orderBy('published_at', 'desc')->paginate(20), 200);
    }
}

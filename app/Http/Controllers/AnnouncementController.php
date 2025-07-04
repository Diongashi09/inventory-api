<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Announcement::class,'announcement');
    }

    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Announcement::query();

        // Always filter for announcements that are published (or scheduled for future)
        // and not expired.
        $query->where(function ($q) {
            $q->whereNull('published_at') // Publish immediately if null
              ->orWhere('published_at', '<=', Carbon::now()); // Or if published_at is in the past/now
        })->where(function ($q) {
            $q->whereNull('expires_at') // Never expires if null
              ->orWhere('expires_at', '>', Carbon::now()); // Or if expires_at is in the future
        });

        // If client, only published (already handled by the above logic, but keeping the scope for clarity)
        // The `published()` scope in your model should ideally handle `published_at <= now()`
        // and `expires_at > now()` or `expires_at IS NULL`.
        // If your `published()` scope only checks `published_at`, you might need to adjust it
        // or rely on the explicit `where` clauses above.
        if ($user->role->name === 'Client') {
            // This condition is now largely covered by the explicit where clauses above.
            // If you have a specific 'published' scope that does more, keep it.
            // $query->published();
        }

        // Order by latest published announcements
        $query->orderByDesc('published_at');

        // Apply limit ONLY if explicitly requested via query parameter
        $limit = $request->query('limit'); // Get limit from query parameter, if present
        if ($limit !== null && is_numeric($limit) && $limit > 0) {
            $query->take((int)$limit);
        }

        $announcements = $query->with('creator')->get(); // Get all if no limit, or limited set

        return response()->json($announcements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data['created_by'] = $request->user()->id;

        // If published_at is not provided, set it to now
        if (empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }

        $announcement = Announcement::create($data);

        return response()->json($announcement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement):JsonResponse
    {
        // For show, you might want to eager load creator as well
        return response()->json($announcement->load('creator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement):JsonResponse
    {
        $data = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'content'      => 'sometimes|required|string',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        // If published_at is being updated to null, set it to now
        // Or if it's explicitly provided as null, treat it as immediate
        if (array_key_exists('published_at', $data) && empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }

        $announcement->update($data);

        return response()->json($announcement->fresh('creator')); // Load creator for fresh response
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(null, 204);
    }
}
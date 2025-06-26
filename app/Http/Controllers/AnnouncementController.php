<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Announcement::class,'announcement');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = Announcement::query();

        // if client, only published
        if ($user->role->name === 'client') {
            $query->published();
        }

        $announcements = $query->orderByDesc('published_at')->get();

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

        $announcement = Announcement::create($data);

        return response()->json($announcement, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement):JsonResponse
    {
        return response()->json($announcement);
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

        $announcement->update($data);

        return response()->json($announcement);
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

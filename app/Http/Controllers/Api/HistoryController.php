<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HistoryProblemResource;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HistoryController extends Controller
{
    /**
     * Get problems created by the user.
     */
    public function createdProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        // Use the relationship defined in Member.php
        $problems = $member->problems()
            ->latest()
            ->paginate(15);

        return response()->json($problems);
    }

    /**
     * Get problems successfully solved by the user.
     */
public function solvedProblems(Request $request, $userId)
{
    $member = Member::findOrFail($userId);

    $problems = $member->solvedProblems()
        ->with('creator')
        ->latest()
        ->paginate(15);

    // This wraps the paginated collection in our new Resource
    return HistoryProblemResource::collection($problems);
}

    /**
     * Get problems the user attempted but hasn't solved yet.
     */
    public function attemptedProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        $problems = $member->attemptedProblems()
            ->with('creator')
            ->latest()
            ->paginate(15);

        return response()->json($problems);
    }

    public function renderHistoryPage()
{
    // Ensure the user is logged in
    $userId = Auth::id();

    // Return the blade view and pass the userId as a prop
    return Inertia::render('Profile/History', [
        'userId' => $userId,
    ]);
}
}

<?php

namespace App\Http\Controllers\Api;

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

        $problems = $member->problems()
            ->with('creator')
            ->latest()
            ->paginate(15);

        return HistoryProblemResource::paginatedProblemResponse($problems);
    }

    /**
     * Get problems successfully solved by the user.
     */
    public function solvedProblems(Request $request, $userId): JsonResponse
    {
        $member = Member::findOrFail($userId);

        $problems = $member->solvedProblems()
            ->with('creator')
            ->latest()
            ->paginate(15);

        return HistoryProblemResource::paginatedProblemResponse($problems);
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

        return HistoryProblemResource::paginatedProblemResponse($problems);
    }

    public function renderHistoryPage()
    {
        $userId = Auth::id();

        return Inertia::render('Profile/History', [
            'userId' => $userId,
        ]);
    }

    
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;
use Inertia\Inertia;
use App\Models\Member;
use App\Models\Submission;
class ProblemBrowsingController extends Controller
{

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $difficulty = strtolower(trim((string) $request->input('difficulty', 'all')));
        $allowedDifficulties = ['all', 'easy', 'medium', 'hard'];

        if (!in_array($difficulty, $allowedDifficulties, true)) {
            $difficulty = 'all';
        }

        $problems = Problem::query()
            ->with('creator:userId,username')
            ->where('visibility', 'public')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($difficulty !== 'all', function ($query) use ($difficulty) {
                $query->whereRaw('LOWER(difficulty) = ?', [$difficulty]);
            })
            ->latest()
            ->paginate(9)
            ->withQueryString()
            ->through(function (Problem $problem) {
                return [
                    'problemId' => $problem->problemId,
                    'title' => $problem->title,
                    'description' => $problem->description,
                    'difficulty' => strtolower((string) $problem->difficulty),
                    'language' => $problem->language,
                    'status' => $problem->status,
                    'creatorName' => $problem->creator?->username,
                    'createdAt' => $problem->created_at->diffForHumans(null, false, false, 2),
                ];
            });

        return Inertia::render('ProblemBrowsing', [
            'problems' => $problems,
            'filters' => [
                'search' => $search,
                'difficulty' => $difficulty,
            ],
        ]);
    }

    public function show(Problem $problem)
    {
        $problem->load('creator:userId,username');
        $testCases = $problem->testCases()->get(['input', 'expected_output']);
        $submissions = $problem->submissions()->with('member:userId,username')->where('userId', auth()->id())->latest()->get();
        $sharedSolutions = $problem->sharedSolutions()->latest()->get();
        return Inertia::render('ProblemDetail', [
            'problem' => [
                'problemId' => $problem->problemId,
                'title' => $problem->title,
                'description' => $problem->description,
                'difficulty' => strtolower((string) $problem->difficulty),
                'status' => $problem->status,
                'creatorId' => $problem->creatorId,
                'creatorName' => $problem->creator?->username,
                'createdAt' => $problem->created_at->diffForHumans(null, false, false, 2),
                'testCases' => $testCases,
                'submissions' => $submissions->map(function ($submission) {
                    return [
                        'username' => $submission->user?->username,
                        'language' => $submission->language,
                        'code' => $submission->code,
                        'status' => $submission->status,
                        'createdAt' => $submission->created_at->diffForHumans(null, false, false, 2),
                    ];
                }),
                'sharedSolutions'=>$sharedSolutions->map(function ($solution) {
                    return [
                        'username' =>Member::find($solution->userId)?->username ?? 'Unknown user',
                        'language' => Submission::find($solution->submissionId)?->language ?? 'Unknown language',
                        'code' => $solution->code,
                        'createdAt' => $solution->created_at->diffForHumans(null, false, false, 2),
                    ];
                })
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;


class ProblemCreationController extends Controller
{
    /**
     * Handle the incoming request to create a new problem.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'description' => ['required', 'string'],
            'solution' => ['required', 'string'],
            'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
            'explanation' => ['nullable', 'string'],
            'visibility' => ['required', 'string', 'in:public,private'],
            'test_cases' => ['required', 'array'],
            'test_cases.*.input' => ['required', 'string'],
            'test_cases.*.expected_output' => ['required', 'string'],
            'test_cases.*.is_default' => ['boolean'],
        ]);

        $problem = Problem::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'solution' => $validated['solution'],
            'explanation' => $validated['explanation'] ?? null,
            'visibility' => $validated['visibility'],
            'difficulty' => $validated['difficulty'],
            'creatorId' => auth()->id(),
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ($validated['test_cases'] as $testCaseData) {
            $problem->testCases()->create([
                'input' => $testCaseData['input'],
                'expected_output' => $testCaseData['expected_output'],
                'is_default' => $testCaseData['is_default'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);
        }
        $problem->submissions()->create([
            'userId' => auth()->id(),
            'code' => $validated['solution'],
            'language' => $validated['language'],
            'status' => 'accepted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $problem->sharedSolutions()->create([
            'userId' => auth()->id(),
            'problemId' => $problem->problemId,
            'submissionId' => $problem->submissions()->latest()->first()->submissionId,
            'title'=> "Creator Solution",
            'explanation'=>$validated['explanation'] ?? null,
            'code' => $validated['solution'],
            'deleted_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Problem created successfully.',
            'data' => [ 
                'id' => $problem->id,
                'title' => $problem->title,
                'description' => $problem->description,
                'solution' => $problem->solution,
                'language' => $problem->language,
                'explanation' => $problem->explanation,
                'visibility' => $problem->visibility,
                'created_at' => $problem->created_at?->toISOString(),
                'updated_at' => $problem->updated_at?->toISOString(),
            ],
        ], 201);
    }
}

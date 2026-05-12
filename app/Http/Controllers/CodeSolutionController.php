<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\Request;

class CodeSolutionController extends Controller
{
    public function store(Request $request, Problem $problem)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string'],
            'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
            'status' => ['required', 'string'],
            'stdout' => ['nullable', 'string'],
            'stderr' => ['nullable', 'string'],
            'compileOutput' => ['nullable', 'string'],
            'compile_output' => ['nullable', 'string'],
            'exit_code' => ['nullable', 'integer'],
            'explanation' => ['nullable', 'string'],
        ]);

        $compileOutput = $validated['compileOutput'] ?? $validated['compile_output'] ?? null;

        $submission = $problem->submissions()->create([
            'userId' => auth()->id(),
            'code' => $validated['code'],
            'language' => $validated['language'],
            'status' => $validated['status'],
            'stdout' => $validated['stdout'] ?? null,
            'stderr' => $validated['stderr'] ?? null,
            'compile_output' => $compileOutput,
            'exit_code' => $validated['exit_code'] ?? null,
        ]);

        $solution = $problem->sharedSolutions()->create([
            'userId' => auth()->id(),
            'submissionId' => $submission->submissionId,
            'title' => $validated['title'],
            'code' => $validated['code'],
            'explanation' => $validated['explanation'] ?? null,
        ]);

        return response()->json([
            'message' => 'Solution shared successfully',
            'data' => [
                'solutionId' => $solution->solutionId,
                'problemId' => $problem->problemId,
                'submissionId' => $submission->submissionId,
                'userId' => auth()->id(),
                'title' => $solution->title,
                'username' => $request->user()?->username ?? '',
                'code' => $validated['code'],
                'language' => $validated['language'],
                'explanation' => $solution->explanation,
                'createdAt' => $solution->created_at->toDateTimeString(),
            ],
        ], 201);
    }
}

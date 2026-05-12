<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;

class CodeSubmissionController extends Controller
{
    public function store(Request $request, Problem $problem){
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
            'status' => ['required', 'string'],
            'stdout' => ['nullable', 'string'],
            'stderr' => ['nullable', 'string'],
            'compileOutput' => ['nullable', 'string'],
            'compile_output' => ['nullable', 'string'],
            'exit_code' => ['nullable', 'integer'],
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

        return response()->json([
            'message' => 'Submission saved successfully',
            'data' => [
                'submissionId' => $submission->submissionId,
                'problemId' => $problem->problemId,
                'userId' => auth()->id(),
                'code' => $submission->code,
                'language' => $submission->language,
                'status' => $submission->status,
                'stdout' => $submission->stdout,
                'stderr' => $submission->stderr,
                'compile_output' => $submission->compile_output,
                'compileOutput' => $submission->compile_output,
                'exit_code' => $submission->exit_code,
                'createdAt' => $submission->created_at->toDateTimeString(),
                'updatedAt' => $submission->updated_at->toDateTimeString(),
            ],
        ], 201);
    }
}

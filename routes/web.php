<?php

use App\Models\Problem;
use App\Models\TestCase;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DiscussionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\Piston\PistonExecutionService;
use App\Http\Controllers\ProblemCreationController;
use App\Http\Controllers\ProblemBrowsingController;
use App\Http\Controllers\CodeSubmissionController;
use App\Http\Controllers\CodeSolutionController;
Route::get('/', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/editor', function () {
    return Inertia::render('Editor');
})->middleware(['auth', 'verified'])->name('editor');

Route::get('/problemcreation', function () {
    return Inertia::render('problemCreation');
})->middleware(['auth', 'verified'])->name('problem-creation.index');

Route::post('/problemcreation', [ProblemCreationController::class, 'store'])->middleware(['auth', 'verified'])->name('problem-creation.store');

Route::post('/discussions/{discussion}/upvote', [DiscussionController::class, 'upvote'])->middleware(['auth', 'verified'])->name('discussions.upvote');
Route::post('/discussions/{discussion}/downvote', [DiscussionController::class, 'downvote'])->middleware(['auth', 'verified'])->name('discussions.downvote');
Route::get('/browse-problems', [ProblemBrowsingController::class, 'index'])->middleware(['auth', 'verified'])->name('browse-problems.index');

Route::get('/browse-problems/{problem}', [ProblemBrowsingController::class, 'show'])->middleware(['auth', 'verified'])->name('browse-problems.show');

Route::post('/browse-problems/{problem}/submission', [CodeSubmissionController::class, 'store'])->middleware(['auth', 'verified'])->name('submissions.store');
Route::post('/browse-problems/{problem}/solution', [CodeSolutionController::class, 'store'])->middleware(['auth', 'verified'])->name('solutions.store');
Route::post('/execute', function (Request $request, PistonExecutionService $piston) {
    $validated = $request->validate([
        'language' => ['required', 'string', 'in:javascript,typescript,python,java,c'],
        'code' => ['required', 'string', 'max:100000'],
    ]);

    $result = $piston->execute($validated['language'], $validated['code']);

    if ($request->expectsJson()) {
        return response()->json([
            'message' => $result->message,
            'data' => [
                'status' => $result->status,
                'stdout' => $result->stdout,
                'stderr' => $result->stderr,
                'compile_output' => $result->compileOutput,
                'exit_code' => $result->exitCode,
                'execution_time_ms' => $result->executionTimeMs,
                'runtime' => $result->runtime,
                'runtime_version' => $result->runtimeVersion,
                'signal' => $result->signal,
            ],
        ]);
    }

    return back()->with([
        'executionResult' => $result->stdout ?? $result->stderr ?? $result->compileOutput ?? $result->message,
    ]);
})->middleware(['auth', 'verified'])->name('execute');

Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/profile/history', [HistoryController::class, 'renderHistoryPage'])->name('history.index');

require __DIR__.'/auth.php';

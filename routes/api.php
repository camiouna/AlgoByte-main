<?php

use App\Http\Controllers\Api\CodeSubmissionController;
use App\Http\Controllers\Api\HistoryController;
use Illuminate\Support\Facades\Route;

Route::post('/code-submissions', [CodeSubmissionController::class, 'store'])
    ->name('api.code-submissions.store');
Route::prefix('v1/users/{userId}/history')->group(function () {
    Route::get('created', [HistoryController::class, 'createdProblems']);
    Route::get('solved', [HistoryController::class, 'solvedProblems']);
    Route::get('attempted', [HistoryController::class, 'attemptedProblems']);
});

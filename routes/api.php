<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects',        [ProjectController::class, 'index']);
    Route::post('/projects',       [ProjectController::class, 'store']);
    Route::get('/projects/{id}',   [ProjectController::class, 'show'])->middleware('project.access');
    Route::match(['put', 'patch'], '/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    Route::get('/projects/{id}/tasks', [ProjectController::class, 'tasks'])->middleware('project.access');
    Route::post('/projects/{id}/tasks', [TaskController::class, 'store'])->middleware('project.access');

    Route::get('/tasks',        [TaskController::class, 'index']);
    Route::get('/tasks/{id}',   [TaskController::class, 'show']);
    Route::match(['put', 'patch'], '/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    Route::get('/tasks/{id}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{id}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

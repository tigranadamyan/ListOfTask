<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| REST API for the To-Do List application.
| All task routes are protected by Sanctum token authentication.
| Task ownership is enforced via TaskPolicy.
|
*/

// Public auth routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require valid Sanctum API token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Task CRUD
    Route::apiResource('tasks', TaskController::class);

    // Change task status (dedicated endpoint for clarity)
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
});

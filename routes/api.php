<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Employer\JobApplicationController;
use App\Http\Controllers\Api\Employer\JobController;
use App\Http\Controllers\Api\Freelancer\ApplicationController;
use App\Http\Controllers\Api\Freelancer\JobListingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — MX100 (prefix /api from RouteServiceProvider + v1 here)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        Route::middleware('role:employer')->prefix('employer')->group(function () {
            Route::get('jobs', [JobController::class, 'index']);
            Route::post('jobs', [JobController::class, 'store']);
            Route::get('jobs/{job}', [JobController::class, 'show']);
            Route::put('jobs/{job}', [JobController::class, 'update']);
            Route::patch('jobs/{job}', [JobController::class, 'update']);
            Route::delete('jobs/{job}', [JobController::class, 'destroy']);
            Route::post('jobs/{job}/publish', [JobController::class, 'publish']);
            Route::get('jobs/{job}/applications', [JobApplicationController::class, 'index']);
        });

        Route::middleware('role:freelancer')->group(function () {
            Route::get('jobs', [JobListingController::class, 'index']);
            Route::get('jobs/{job}', [JobListingController::class, 'show']);
            Route::post('jobs/{job}/applications', [ApplicationController::class, 'store']);
        });
    });
});

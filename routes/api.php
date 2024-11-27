<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware(['is_admin'])->group(function () {
        Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
        Route::apiResource('tags', App\Http\Controllers\Api\TagController::class);
    });

    Route::apiResource('posts', App\Http\Controllers\Api\PostController::class);
    Route::apiResource('posts.comments', App\Http\Controllers\Api\CommentController::class);
    Route::patch('posts/{post}/like', [App\Http\Controllers\Api\PostController::class, 'like']);
    Route::patch('posts/{post}/unlike', [App\Http\Controllers\Api\PostController::class, 'unlike']);

    Route::get('profile', [App\Http\Controllers\Api\ProfileController::class, 'index']);
    Route::put('profile', [App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::delete('profile', [App\Http\Controllers\Api\ProfileController::class, 'destroy']);

    Route::post('logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
});

Route::post('register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login']);

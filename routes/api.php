<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix("/notes")->group(function(){
        Route::get('', [NoteController::class, 'index']);
        Route::post('', [NoteController::class, 'store']);
        Route::get('/{slug}', [NoteController::class, 'show']);
        Route::put('/{slug}', [NoteController::class, 'update']);
        Route::delete('/{slug}', [NoteController::class, 'destroy']);
    });
    
    Route::prefix("/categories")->group(function(){
        Route::get('', [CategoryController::class, 'index']);
        Route::get('/{id}/notes', [NoteController::class, 'showNotes']);
    });

    Route::prefix("/authors")->group(function() {
        Route::get('/{authorId}/notes', [NoteController::class, 'getByAuthor']);
        Route::get('/{authorId}/follows', [UserController::class, 'followToggle']);
        Route::get('/{authorId}/stats', [UserController::class, 'getStats']);
    });

    Route::prefix("/my")->group(function() {
        Route::get('/notes', [NoteController::class, 'myNotes']);
        Route::get('/follows', [UserController::class, 'myFollowStats']);
    });
});

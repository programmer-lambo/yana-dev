<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view("/login", "auth.login")->name("login");
Route::view("/register", "auth.register")->name("register");

Route::view("/", "dashboard")->name("dashboard");
Route::view("/dashboard", "dashboard")->name("dashboard");

Route::view('/notes/{slug}', 'notes.show')->name('notes.show');
Route::view('/categories', 'categories.index')->name('categories.index');;
Route::view('/categories/{id}/notes', 'categories.notes')->name('categories.notes');;
Route::view('/authors/{id}/notes','notes.listByAuthor')->name('notes.listByAuthor');

// Route::get('/notes/{slug}', function(){ 
//     return view('notes.show');
// })->name('notes.show');

// Route::get('/categories', fn() => view('categories.index'))->name('categories.index');
// Route::get('/categories/{id}/notes', fn() => view('categories.notes'))->name('categories.notes');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view("/login", "auth.login")->name("login");
Route::view("/register", "auth.register")->name("register");

Route::view("/", "home")->name("root");
Route::view("/home", "home")->name("home");

Route::view('/notes/{slug}', 'notes.show')->name('notes.show');
Route::view('/categories', 'categories.index')->name('categories.index');;
Route::view('/categories/{id}/notes', 'categories.notes')->name('categories.notes');;
Route::view('/authors/{id}/notes','notes.listByAuthor')->name('notes.listByAuthor');

Route::view('/dashboard/notes','dashboard.notes')->name('dashboard.notes');
Route::redirect('/dashboard', '/dashboard/notes', 301);
Route::view('/dashboard/notes/create','dashboard.notes-create')->name('dashboard.notes.create');
Route::view('/dashboard/notes/{slug}/edit','dashboard.notes-edit')->name('dashboard.notes.edit');

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

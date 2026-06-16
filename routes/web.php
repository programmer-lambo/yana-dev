<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view("/login", "auth.login")->name("login");
Route::view("/register", "auth.register")->name("register");

Route::view("/", "dashboard")->name("dashboard");
Route::view("/dashboard", "dashboard")->name("dashboard");

Route::get('/notes/{slug}', function(){ 
    return view('notes.show');
})->name('notes.show');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

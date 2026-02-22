<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Placeholders for post methods
Route::post('/login', function () { return 'Login Logic Here'; });
Route::post('/register', function () { return 'Register Logic Here'; });


<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'check2fa'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'check2fa'])
    ->name('profile');

require __DIR__.'/auth.php';

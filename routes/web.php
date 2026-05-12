<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'check2fa'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'check2fa'])
    ->name('profile');

// ── Sistema (admin only) ──────────────────────────────────────────────────────
Route::middleware(['auth', 'check2fa'])->prefix('sistema')->name('admin.sistema.')->group(function () {
    Volt::route('menus', 'admin.system.menu-manager')
        ->name('menus');
    Volt::route('configuracoes', 'admin.system.system-settings')
        ->name('configuracoes');
});

require __DIR__.'/auth.php';

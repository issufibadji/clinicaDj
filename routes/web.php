<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['pt', 'en', 'fr'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'check2fa'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'check2fa'])
    ->name('profile');

// ── Sistema (admin only) ──────────────────────────────────────────────────────
Route::middleware(['auth', 'check2fa'])->prefix('sistema')->name('admin.sistema.')->group(function () {
    Volt::route('auditoria', 'admin.system.audit-log')
        ->name('auditoria');
    Volt::route('menus', 'admin.system.menu-manager')
        ->name('menus');
    Volt::route('configuracoes', 'admin.system.system-settings')
        ->name('configuracoes');
});

require __DIR__.'/profile.php';
require __DIR__.'/admin.php';
require __DIR__.'/modules.php';
require __DIR__.'/auth.php';

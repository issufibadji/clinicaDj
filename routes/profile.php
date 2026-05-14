<?php

use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified', 'check2fa'])->group(function () {
    Volt::route('/perfil', 'profile.user-profile')
        ->name('profile.show');

    Volt::route('/perfil/configuracoes', 'profile.account-settings')
        ->name('profile.settings');

    Volt::route('/perfil/perfis', 'profile.user-profiles')
        ->name('profile.profiles');
});

// Seleção de perfil após login (sem 'verified')
Route::middleware(['auth', 'check2fa'])->group(function () {
    Volt::route('/selecionar-perfil', 'pages.auth.profile-selector')
        ->name('auth.select-profile');
});

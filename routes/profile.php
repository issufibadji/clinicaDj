<?php

use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified', 'check2fa'])->group(function () {
    Volt::route('/perfil', 'profile.user-profile')
        ->name('profile.show');

    Volt::route('/perfil/configuracoes', 'profile.account-settings')
        ->name('profile.settings');
});

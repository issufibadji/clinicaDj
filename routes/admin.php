<?php

use App\Http\Controllers\Impersonation\StartImpersonationController;
use App\Http\Controllers\Impersonation\StopImpersonationController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth', 'check2fa'])->prefix('admin')->name('admin.')->group(function () {

    // ── Impersonação ──────────────────────────────────────────────────────────
    Route::post('impersonar/{user}', StartImpersonationController::class)
        ->middleware('role:admin')
        ->name('impersonar');

    Route::post('impersonacao/encerrar', StopImpersonationController::class)
        ->name('impersonacao.encerrar');

    Volt::route('impersonacao', 'admin.impersonation.impersonation-history')
        ->middleware('role:admin')
        ->name('impersonation.history');

    // Perfis de usuário (admin)
    Volt::route('usuarios/{user}/perfis', 'admin.access-control.user-profile-manager')
        ->middleware('permission:users.edit')
        ->name('usuarios.profiles');

    // Permissões
    Volt::route('permissoes', 'admin.permissions.permission-manager')
        ->middleware('permission:permissions.view')
        ->name('permissoes.index');

    // Papéis
    Volt::route('papeis', 'admin.roles.role-manager')
        ->middleware('permission:roles.view')
        ->name('papeis.index');

    // Usuários
    Volt::route('usuarios', 'admin.users.user-manager')
        ->middleware('permission:users.view')
        ->name('usuarios.index');

    // Vínculo usuário–papel
    Volt::route('vinculo', 'admin.user-roles.user-role-assignment')
        ->middleware('permission:users.assign_roles')
        ->name('vinculo.index');

    // Notificações administrativas
    Volt::route('notificacoes', 'admin.notifications.notification-manager')
        ->middleware('role:admin|super-admin')
        ->name('notificacoes.index');
});

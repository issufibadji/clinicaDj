<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Políticas serão registradas aqui conforme os módulos forem criados.
        // Exemplo:
        // Gate::policy(User::class, UserPolicy::class);
        // Gate::policy(Role::class, RolePolicy::class);
        // Gate::policy(Permission::class, PermissionPolicy::class);

        // Usuários admin (level 1) passam por todas as gates automaticamente
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
    }
}

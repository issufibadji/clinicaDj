<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

// ── Helpers globais ───────────────────────────────────────────────────────────

/**
 * Limpa cache do Spatie Permission (necessário entre testes com roles/permissions).
 */
function resetPermissions(): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

/**
 * Cria e retorna um usuário admin com o papel "admin".
 */
function makeAdmin(array $attributes = []): User
{
    resetPermissions();
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
    $user->assignRole($role);
    return $user;
}

/**
 * Cria usuário com papel e conjunto de permissões específicos.
 */
function makeUserWithRole(string $roleName, array $permissions = [], array $attributes = []): User
{
    resetPermissions();
    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

    foreach ($permissions as $perm) {
        $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        $role->givePermissionTo($p);
    }

    $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
    $user->assignRole($role);
    return $user;
}

/**
 * Cria usuário inativo (sem papel específico).
 */
function makeInactiveUser(): User
{
    return User::factory()->create(['is_active' => false]);
}

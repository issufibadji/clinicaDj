<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

// ── Usuário inativo ───────────────────────────────────────────────────────────

test('inactive user is redirected to login when accessing dashboard', function () {
    $user = makeInactiveUser();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('login'));
});

test('inactive user is logged out on any web request', function () {
    $user = makeInactiveUser();

    $this->actingAs($user)
        ->get('/dashboard');

    $this->assertGuest();
});

test('active user can access dashboard', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

// ── Permissões por papel ──────────────────────────────────────────────────────

test('user without permission gets 403 on departments route', function () {
    $user = makeUserWithRole('recepcionista', []);

    $this->actingAs($user)
        ->get(route('departments.index'))
        ->assertForbidden();
});

test('user with permission can access departments route', function () {
    $user = makeUserWithRole('recepcionista', ['departments.view']);

    $this->actingAs($user)
        ->get(route('departments.index'))
        ->assertOk();
});

test('user without permission gets 403 on payments route', function () {
    $user = makeUserWithRole('medico', []);

    $this->actingAs($user)
        ->get(route('payments.index'))
        ->assertForbidden();
});

test('user with payments.view permission can access payments route', function () {
    $user = makeUserWithRole('financeiro', ['payments.view']);

    $this->actingAs($user)
        ->get(route('payments.index'))
        ->assertOk();
});

test('user without permission gets 403 on admin permissions route', function () {
    $user = makeUserWithRole('medico', []);

    $this->actingAs($user)
        ->get(route('admin.permissoes.index'))
        ->assertForbidden();
});

test('user with permissions.view can access admin permissions route', function () {
    $user = makeUserWithRole('admin_staff', ['permissions.view']);

    $this->actingAs($user)
        ->get(route('admin.permissoes.index'))
        ->assertOk();
});

// ── Admin bypass ──────────────────────────────────────────────────────────────

test('admin role bypasses all permission checks', function () {
    $admin = makeAdmin();

    $this->actingAs($admin)
        ->get(route('departments.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('payments.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.permissoes.index'))
        ->assertOk();
});

// ── Rotas protegidas por auth ─────────────────────────────────────────────────

test('unauthenticated user is redirected to login from dashboard', function () {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login from clinical routes', function () {
    $this->get(route('patients.index'))
        ->assertRedirect(route('login'));
});

<?php

use App\Actions\Impersonation\StartImpersonation;
use App\Actions\Impersonation\StopImpersonation;
use App\Exceptions\ImpersonationException;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeImpersonationAdmin(): User
{
    resetPermissions();
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $user = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
    $user->assignRole($role);
    return $user;
}

function makeImpersonationTarget(string $roleName = 'medico'): User
{
    resetPermissions();
    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
    $user = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);
    $user->assignRole($role);
    return $user;
}

// ── Testes ────────────────────────────────────────────────────────────────────

it('admin can start impersonation and log is created', function () {
    $admin  = makeImpersonationAdmin();
    $target = makeImpersonationTarget();

    $log = app(StartImpersonation::class)->handle($admin, $target, 'Teste');

    expect($log)->toBeInstanceOf(ImpersonationLog::class)
        ->and($log->admin_id)->toBe($admin->id)
        ->and($log->target_id)->toBe($target->id)
        ->and($log->notes)->toBe('Teste')
        ->and($log->ended_at)->toBeNull();

    expect(Auth::id())->toBe($target->id);
    expect(session('impersonating'))->toBeTrue();
    expect(session('original_user_id'))->toBe($admin->id);
});

it('stops impersonation and restores admin session', function () {
    $admin  = makeImpersonationAdmin();
    $target = makeImpersonationTarget();

    app(StartImpersonation::class)->handle($admin, $target);

    $restored = app(StopImpersonation::class)->handle('manual');

    expect($restored->id)->toBe($admin->id);
    expect(Auth::id())->toBe($admin->id);
    expect(session('impersonating'))->toBeNull();
    expect(session('impersonation_log_id'))->toBeNull();

    $log = ImpersonationLog::latest()->first();
    expect($log->ended_at)->not->toBeNull();
    expect($log->end_reason)->toBe('manual');
});

it('cannot impersonate another admin', function () {
    $admin       = makeImpersonationAdmin();
    $otherAdmin  = makeImpersonationAdmin();

    expect(fn () => app(StartImpersonation::class)->handle($admin, $otherAdmin))
        ->toThrow(ImpersonationException::class);
});

it('cannot impersonate self', function () {
    $admin = makeImpersonationAdmin();

    expect(fn () => app(StartImpersonation::class)->handle($admin, $admin))
        ->toThrow(ImpersonationException::class);
});

it('non-admin cannot start impersonation', function () {
    $medico = makeImpersonationTarget('medico');
    $target = makeImpersonationTarget('recepcionista');

    expect(fn () => app(StartImpersonation::class)->handle($medico, $target))
        ->toThrow(ImpersonationException::class);
});

it('cannot nest impersonation when already impersonating', function () {
    $admin   = makeImpersonationAdmin();
    $target1 = makeImpersonationTarget('medico');
    $target2 = makeImpersonationTarget('recepcionista');

    app(StartImpersonation::class)->handle($admin, $target1);

    expect(fn () => app(StartImpersonation::class)->handle($admin, $target2))
        ->toThrow(ImpersonationException::class);
});

it('blocks DELETE requests during impersonation', function () {
    session([
        'impersonating'         => true,
        'impersonation_log_id'  => 'fake-log-id',
        'impersonation_expires' => now()->addHours(2)->timestamp,
    ]);

    $middleware = new \App\Http\Middleware\BlockDestructiveActionsOnImpersonation();
    $request    = \Illuminate\Http\Request::create('/any', 'DELETE');
    $request->setLaravelSession(session()->driver());

    $response = $middleware->handle($request, fn () => response('ok', 200));

    // No JSON accept header → redirects back with flash error
    expect($response->getStatusCode())->toBe(302);
    expect(session('error'))->toBe(__('Ação não permitida durante impersonação.'));
});

it('stop action throws when not impersonating', function () {
    expect(fn () => app(StopImpersonation::class)->handle())
        ->toThrow(ImpersonationException::class);
});

it('impersonation via http route creates log and redirects to dashboard', function () {
    $admin  = makeImpersonationAdmin();
    $target = makeImpersonationTarget();

    $this->actingAs($admin)
        ->post(route('admin.impersonar', $target))
        ->assertRedirect(route('dashboard'));

    expect(ImpersonationLog::where('admin_id', $admin->id)->where('target_id', $target->id)->exists())
        ->toBeTrue();
});

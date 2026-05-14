<?php

use App\Actions\Admin\Profiles\CreateUserProfile;
use App\Actions\Admin\Profiles\SetDefaultProfile;
use App\Actions\Admin\Profiles\SwitchActiveProfile;
use App\Models\User;
use App\Models\UserProfile;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

// ── Helpers ───────────────────────────────────────────────────────────────────

function createUserWithProfiles(int $count, array $extraRoles = []): User
{
    resetPermissions();

    $roleNames = ['admin', 'medico', 'recepcionista', 'financeiro'];
    $user = User::factory()->create(['is_active' => true, 'email_verified_at' => now()]);

    for ($i = 0; $i < $count; $i++) {
        $roleName = $roleNames[$i] ?? "role_{$i}";
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->assignRole($role);

        $profile = UserProfile::create([
            'user_id'    => $user->id,
            'role_id'    => $role->id,
            'color'      => '#10B981',
            'is_default' => $i === 0,
            'is_active'  => true,
        ]);

        if ($i === 0) {
            $user->updateQuietly(['active_profile_id' => $profile->id]);
        }
    }

    return $user->fresh();
}

// ── Testes ────────────────────────────────────────────────────────────────────

it('redirects directly when user has single profile', function () {
    $user = createUserWithProfiles(1);

    $this->actingAs($user)
        ->get(route('auth.select-profile'))
        ->assertRedirect(route('dashboard'));
});

it('shows profile selector for multi-profile user', function () {
    $user = createUserWithProfiles(2);
    // Remove o active_profile_id para forçar exibição do seletor
    $user->updateQuietly(['active_profile_id' => null]);

    $this->actingAs($user)
        ->get(route('auth.select-profile'))
        ->assertOk();
});

it('switches active profile via action', function () {
    $user = createUserWithProfiles(2);
    $newProfile = $user->profiles()->where('is_default', false)->first();

    app(SwitchActiveProfile::class)->handle($user, $newProfile->id);

    expect($user->fresh()->active_profile_id)->toBe($newProfile->id);
});

it('cannot switch to another users profile', function () {
    $user1 = createUserWithProfiles(1);
    $user2 = createUserWithProfiles(1);

    expect(fn () => app(SwitchActiveProfile::class)->handle($user1, $user2->profiles->first()->id))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

it('cannot delete the only active profile', function () {
    $user = createUserWithProfiles(1);
    $profileId = $user->profiles->first()->id;

    $action = app(\App\Actions\Admin\Profiles\DeleteUserProfile::class);

    expect(fn () => $action->handle($user, $profileId))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('cannot delete currently active profile', function () {
    $user = createUserWithProfiles(2);
    $activeProfileId = $user->active_profile_id;

    $action = app(\App\Actions\Admin\Profiles\DeleteUserProfile::class);

    expect(fn () => $action->handle($user, $activeProfileId))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('sets default profile correctly', function () {
    $user = createUserWithProfiles(2);
    $second = $user->profiles()->where('is_default', false)->first();

    app(SetDefaultProfile::class)->handle($user, $second->id);

    expect($user->profiles()->where('is_default', true)->first()->id)->toBe($second->id)
        ->and($user->profiles()->where('is_default', true)->count())->toBe(1);
});

it('cannot create duplicate profile for same role', function () {
    $user = createUserWithProfiles(1);
    $existingRole = $user->profiles->first()->role;

    expect(fn () => app(CreateUserProfile::class)->handle($user, ['role_id' => $existingRole->id, 'color' => '#10B981']))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('user model has profile relationships', function () {
    $user = createUserWithProfiles(2);

    expect($user->profiles)->toHaveCount(2)
        ->and($user->activeProfile)->not->toBeNull()
        ->and($user->defaultProfile)->not->toBeNull();
});

it('switches profile via Volt component', function () {
    $user = createUserWithProfiles(2);
    $other = $user->profiles()->where('id', '!=', $user->active_profile_id)->first();

    Volt::actingAs($user)
        ->test('shared.profile-switcher')
        ->call('switchTo', $other->id);

    expect($user->fresh()->active_profile_id)->toBe($other->id);
});

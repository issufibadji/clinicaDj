<?php

use App\Models\User;

test('initials returns two letters from first and last name', function () {
    $user = User::factory()->make(['name' => 'João Silva']);
    expect($user->initials())->toBe('JS');
});

test('initials returns two letters from single name', function () {
    $user = User::factory()->make(['name' => 'Marcos']);
    expect($user->initials())->toBe('MA');
});

test('initials handles three-word names', function () {
    $user = User::factory()->make(['name' => 'Ana Paula Santos']);
    expect($user->initials())->toBe('AS');
});

test('hasTwoFactorEnabled returns false when not configured', function () {
    $user = User::factory()->make(['two_factor_confirmed_at' => null]);
    expect($user->hasTwoFactorEnabled())->toBeFalse();
});

test('hasTwoFactorEnabled returns true when configured', function () {
    $user = User::factory()->make(['two_factor_confirmed_at' => now()]);
    expect($user->hasTwoFactorEnabled())->toBeTrue();
});

test('avatarUrl returns null when no avatar set', function () {
    $user = User::factory()->make(['avatar' => null]);
    expect($user->avatarUrl())->toBeNull();
});

test('avatarUrl returns full url when avatar is set', function () {
    $user = User::factory()->make(['avatar' => 'avatars/test.jpg']);
    expect($user->avatarUrl())->toContain('avatars/test.jpg');
});

test('getRecoveryCodes returns empty array when none set', function () {
    $user = User::factory()->make(['two_factor_recovery_codes' => null]);
    expect($user->getRecoveryCodes())->toBe([]);
});

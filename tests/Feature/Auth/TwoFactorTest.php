<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('2fa challenge screen renders when user has 2fa enabled', function () {
    $user = User::factory()->create([
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertRedirect(route('two-factor.challenge'));
});

test('2fa challenge screen is skipped for users without 2fa', function () {
    $user = User::factory()->create([
        'two_factor_secret'       => null,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertOk();
});

test('2fa challenge screen can be rendered', function () {
    $user = User::factory()->create([
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user);

    $this->get(route('two-factor.challenge'))
        ->assertOk()
        ->assertSeeVolt('pages.auth.two-factor-challenge');
});

test('invalid totp code is rejected', function () {
    $user = User::factory()->create([
        'two_factor_secret'       => encrypt('JBSWY3DPEHPK3PXP'),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->actingAs($user);

    Volt::test('pages.auth.two-factor-challenge')
        ->set('code', '000000')
        ->call('verify')
        ->assertHasErrors(['code']);
});

test('inactive user is blocked from dashboard', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('login'));
});

test('login rate limiter blocks after 5 attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $_) {
        Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password')
            ->call('login');
    }

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email']);
});

<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->user = User::factory()->create(['is_active' => true]);
    $this->actingAs($this->user);
});

// ── Página principal ──────────────────────────────────────────────────────────

test('dashboard page renders with sub-components', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSeeLivewire('dashboard.stats-cards')
        ->assertSeeLivewire('dashboard.appointment-chart')
        ->assertSeeLivewire('dashboard.doctor-on-duty')
        ->assertSeeLivewire('dashboard.mini-calendar');
});

test('dashboard shows user name', function () {
    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee($this->user->name);
});

// ── StatsCards ────────────────────────────────────────────────────────────────

test('stats cards shows correct appointments today count', function () {
    Appointment::factory()->count(3)->create([
        'scheduled_at' => now(),
        'patient_id'   => Patient::factory()->create()->id,
        'doctor_id'    => Doctor::factory()->create()->id,
    ]);

    Volt::test('dashboard.stats-cards')
        ->assertSee('3');
});

test('stats cards shows available doctors count', function () {
    Doctor::factory()->count(2)->create(['is_available' => true]);
    Doctor::factory()->count(1)->create(['is_available' => false]);

    Volt::test('dashboard.stats-cards')
        ->assertSee('2');
});

test('stats cards shows revenue today', function () {
    $appointment = Appointment::factory()->create([
        'patient_id' => Patient::factory()->create()->id,
        'doctor_id'  => Doctor::factory()->create()->id,
    ]);

    Payment::factory()->create([
        'appointment_id' => $appointment->id,
        'amount'         => 150.00,
        'status'         => 'paid',
        'paid_at'        => now(),
    ]);

    Volt::test('dashboard.stats-cards')
        ->assertSee('150');
});

// ── MiniCalendar ──────────────────────────────────────────────────────────────

test('mini calendar renders current month', function () {
    $monthNames = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
        5 => 'Maio',    6 => 'Junho',     7 => 'Julho',     8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    Volt::test('dashboard.mini-calendar')
        ->assertSee($monthNames[now()->month])
        ->assertSee((string) now()->year);
});

test('mini calendar navigates to previous month', function () {
    $monthNames = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
        5 => 'Maio',    6 => 'Junho',     7 => 'Julho',     8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    $prev = now()->subMonth();

    Volt::test('dashboard.mini-calendar')
        ->call('prevMonth')
        ->assertSet('month', $prev->month)
        ->assertSet('year', $prev->year)
        ->assertSee($monthNames[$prev->month]);
});

test('mini calendar navigates to next month', function () {
    $monthNames = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
        5 => 'Maio',    6 => 'Junho',     7 => 'Julho',     8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    $next = now()->addMonth();

    Volt::test('dashboard.mini-calendar')
        ->call('nextMonth')
        ->assertSet('month', $next->month)
        ->assertSet('year', $next->year)
        ->assertSee($monthNames[$next->month]);
});

// ── AppointmentChart ──────────────────────────────────────────────────────────

test('appointment chart renders without errors', function () {
    Volt::test('dashboard.appointment-chart')
        ->assertOk();
});

// ── DoctorOnDuty ─────────────────────────────────────────────────────────────

test('doctor on duty shows empty state when no available doctors', function () {
    Volt::test('dashboard.doctor-on-duty')
        ->assertSee('Nenhum médico disponível');
});

test('doctor on duty shows available doctor name', function () {
    $doctor = Doctor::factory()->create(['is_available' => true]);

    Volt::test('dashboard.doctor-on-duty')
        ->assertSee($doctor->user->name);
});

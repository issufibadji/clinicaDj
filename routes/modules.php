<?php

use Livewire\Volt\Volt;

Route::middleware(['auth', 'verified', 'check2fa'])->group(function () {

    Volt::route('/departamentos', 'clinica.departments')
        ->middleware('permission:departments.view')
        ->name('departments.index');

    Volt::route('/convenios', 'clinica.insurance')
        ->middleware('permission:insurance.view')
        ->name('insurance.index');

    Volt::route('/salas', 'clinica.rooms')
        ->middleware('permission:rooms.view')
        ->name('rooms.index');

    Volt::route('/medicos', 'clinica.doctors')
        ->middleware('permission:doctors.view')
        ->name('doctors.index');

    Volt::route('/pacientes', 'clinica.patients')
        ->middleware('permission:patients.view')
        ->name('patients.index');

    Volt::route('/agendamentos', 'clinica.appointments')
        ->middleware('permission:appointments.view')
        ->name('appointments.index');

    Volt::route('/pagamentos', 'clinica.payments')
        ->middleware('permission:payments.view')
        ->name('payments.index');

    Volt::route('/despesas', 'clinica.expenses')
        ->middleware('permission:payments.view')
        ->name('expenses.index');

    Volt::route('/eventos', 'clinica.events')
        ->middleware('permission:events.view')
        ->name('events.index');

    Volt::route('/chat', 'clinica.chat')
        ->middleware('permission:chat.view')
        ->name('chat.index');
});

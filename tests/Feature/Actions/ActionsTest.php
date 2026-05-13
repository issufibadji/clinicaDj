<?php

use App\Actions\Clinica\Appointments\CreateAppointmentAction;
use App\Actions\Clinica\Appointments\DeleteAppointmentAction;
use App\Actions\Clinica\Appointments\UpdateAppointmentAction;
use App\Actions\Clinica\Departments\CreateDepartmentAction;
use App\Actions\Clinica\Departments\DeleteDepartmentAction;
use App\Actions\Clinica\Departments\UpdateDepartmentAction;
use App\Actions\Clinica\Patients\CreatePatientAction;
use App\Actions\Clinica\Patients\DeletePatientAction;
use App\Actions\Clinica\Patients\UpdatePatientAction;
use App\Actions\Clinica\Payments\CreatePaymentAction;
use App\Actions\Clinica\Payments\DeletePaymentAction;
use App\Actions\Clinica\Chat\SendMessageAction;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;

// ── Department Actions ────────────────────────────────────────────────────────

test('CreateDepartmentAction creates department', function () {
    $dept = app(CreateDepartmentAction::class)->handle('Cardiologia', 'Desc');

    expect($dept->name)->toBe('Cardiologia')
        ->and($dept->description)->toBe('Desc')
        ->and($dept->is_active)->toBeTrue();
});

test('UpdateDepartmentAction updates fields', function () {
    $dept = Department::factory()->create(['name' => 'Antiga']);

    app(UpdateDepartmentAction::class)->handle($dept, 'Nova', null, true);

    expect($dept->fresh()->name)->toBe('Nova');
});

test('DeleteDepartmentAction deletes department with no relations', function () {
    $dept = Department::factory()->create();

    app(DeleteDepartmentAction::class)->handle($dept);

    $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
});

test('DeleteDepartmentAction throws when department has rooms', function () {
    $dept = Department::factory()->create();
    Room::factory()->forDepartment($dept)->create();

    expect(fn() => app(DeleteDepartmentAction::class)->handle($dept))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('DeleteDepartmentAction throws when department has doctors', function () {
    $dept = Department::factory()->create();
    Doctor::factory()->create(['department_id' => $dept->id]);

    expect(fn() => app(DeleteDepartmentAction::class)->handle($dept))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

// ── Patient Actions ───────────────────────────────────────────────────────────

test('CreatePatientAction creates patient', function () {
    $patient = app(CreatePatientAction::class)->handle(
        'Ana Silva', '111.222.333-44', '1990-01-15', '(11) 99999-0000', null, null
    );

    expect($patient->name)->toBe('Ana Silva')
        ->and($patient->cpf)->toBe('111.222.333-44');

    $this->assertDatabaseHas('patients', ['cpf' => '111.222.333-44']);
});

test('UpdatePatientAction updates patient phone', function () {
    $patient = Patient::factory()->create(['phone' => '(11) 00000-0000']);

    app(UpdatePatientAction::class)->handle(
        $patient, $patient->name, $patient->cpf,
        $patient->birth_date->format('Y-m-d'), '(11) 99999-9999', null, null
    );

    expect($patient->fresh()->phone)->toBe('(11) 99999-9999');
});

test('DeletePatientAction deletes patient with no appointments', function () {
    $patient = Patient::factory()->create();

    app(DeletePatientAction::class)->handle($patient);

    $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
});

test('DeletePatientAction throws when patient has appointments', function () {
    $patient = Patient::factory()->create();
    Appointment::factory()->create(['patient_id' => $patient->id]);

    expect(fn() => app(DeletePatientAction::class)->handle($patient))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

// ── Appointment Actions ───────────────────────────────────────────────────────

test('CreateAppointmentAction creates appointment', function () {
    $patient = Patient::factory()->create();
    $doctor  = Doctor::factory()->create();
    $at      = now()->addDay()->format('Y-m-d H:i:s');

    $appt = app(CreateAppointmentAction::class)->handle(
        $patient->id, $doctor->id, null, $at, null
    );

    expect($appt->patient_id)->toBe($patient->id)
        ->and($appt->status)->toBe('scheduled');
});

test('UpdateAppointmentAction updates status to completed', function () {
    $appt = Appointment::factory()->create(['status' => 'scheduled']);

    app(UpdateAppointmentAction::class)->handle(
        $appt, $appt->patient_id, $appt->doctor_id, null,
        $appt->scheduled_at->format('Y-m-d H:i:s'), 'completed', null
    );

    expect($appt->fresh()->status)->toBe('completed');
});

test('DeleteAppointmentAction deletes appointment with no payment', function () {
    $appt = Appointment::factory()->create();

    app(DeleteAppointmentAction::class)->handle($appt);

    $this->assertDatabaseMissing('appointments', ['id' => $appt->id]);
});

test('DeleteAppointmentAction throws when appointment has payment', function () {
    $appt    = Appointment::factory()->create();
    Payment::factory()->create(['appointment_id' => $appt->id]);

    expect(fn() => app(DeleteAppointmentAction::class)->handle($appt))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

// ── Payment Actions ───────────────────────────────────────────────────────────

test('CreatePaymentAction creates payment and sets paid_at when status is paid', function () {
    $appt = Appointment::factory()->create();

    $pay = app(CreatePaymentAction::class)->handle($appt->id, 200.00, 'pix', 'paid', null);

    expect($pay->status)->toBe('paid')
        ->and($pay->paid_at)->not->toBeNull();
});

test('CreatePaymentAction does not set paid_at when status is pending', function () {
    $appt = Appointment::factory()->create();

    $pay = app(CreatePaymentAction::class)->handle($appt->id, 100.00, 'card', 'pending', null);

    expect($pay->paid_at)->toBeNull();
});

test('DeletePaymentAction throws when payment is paid', function () {
    $appt = Appointment::factory()->create();
    $pay  = Payment::factory()->paid()->create(['appointment_id' => $appt->id]);

    expect(fn() => app(DeletePaymentAction::class)->handle($pay))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('DeletePaymentAction deletes pending payment', function () {
    $appt = Appointment::factory()->create();
    $pay  = Payment::factory()->create(['appointment_id' => $appt->id, 'status' => 'pending']);

    app(DeletePaymentAction::class)->handle($pay);

    $this->assertDatabaseMissing('payments', ['id' => $pay->id]);
});

// ── Chat Action ───────────────────────────────────────────────────────────────

test('SendMessageAction stores message in database', function () {
    $from = User::factory()->create();
    $to   = User::factory()->create();

    app(SendMessageAction::class)->handle($from, $to, 'Olá, tudo bem?');

    $this->assertDatabaseHas('chat_messages', [
        'from_user_id' => $from->id,
        'to_user_id'   => $to->id,
        'body'         => 'Olá, tudo bem?',
    ]);
});

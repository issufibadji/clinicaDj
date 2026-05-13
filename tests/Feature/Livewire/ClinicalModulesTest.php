<?php

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->admin = makeAdmin();
    $this->actingAs($this->admin);
});

// ── Departments ───────────────────────────────────────────────────────────────

test('departments page lists existing departments', function () {
    $dept = Department::factory()->create(['name' => 'Cardiologia']);

    Volt::test('clinica.departments')
        ->assertSee('Cardiologia');
});

test('can create department via livewire component', function () {
    Volt::test('clinica.departments')
        ->call('openCreate')
        ->assertSet('showForm', true)
        ->set('formName', 'Neurologia')
        ->set('formDescription', 'Departamento de neurologia')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showForm', false);

    $this->assertDatabaseHas('departments', ['name' => 'Neurologia']);
});

test('department create validates required name', function () {
    Volt::test('clinica.departments')
        ->call('openCreate')
        ->set('formName', '')
        ->call('save')
        ->assertHasErrors(['formName']);
});

test('can edit department via livewire component', function () {
    $dept = Department::factory()->create(['name' => 'Ortopedia']);

    Volt::test('clinica.departments')
        ->call('openEdit', $dept->id)
        ->assertSet('showForm', true)
        ->assertSet('formName', 'Ortopedia')
        ->set('formName', 'Ortopedia Atualizada')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('departments', ['name' => 'Ortopedia Atualizada']);
});

test('can delete department via livewire component', function () {
    $dept = Department::factory()->create();

    Volt::test('clinica.departments')
        ->call('confirmDelete', $dept->id)
        ->assertSet('confirmId', $dept->id)
        ->call('delete');

    $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
});

test('department with rooms cannot be deleted', function () {
    $dept = Department::factory()->create();
    Room::factory()->forDepartment($dept)->create();

    Volt::test('clinica.departments')
        ->call('confirmDelete', $dept->id)
        ->call('delete');

    $this->assertDatabaseHas('departments', ['id' => $dept->id]);
});

// ── Patients ──────────────────────────────────────────────────────────────────

test('patients page lists existing patients', function () {
    $patient = Patient::factory()->create(['name' => 'João Teste']);

    Volt::test('clinica.patients')
        ->assertSee('João Teste');
});

test('can create patient via livewire component', function () {
    Volt::test('clinica.patients')
        ->call('openCreate')
        ->set('formName', 'Maria Silva')
        ->set('formCpf', '123.456.789-00')
        ->set('formBirthDate', '1990-05-15')
        ->set('formPhone', '(11) 99999-0000')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('patients', ['name' => 'Maria Silva', 'cpf' => '123.456.789-00']);
});

test('patient create validates required cpf uniqueness', function () {
    $existing = Patient::factory()->create(['cpf' => '111.222.333-44']);

    Volt::test('clinica.patients')
        ->call('openCreate')
        ->set('formName', 'Outro Paciente')
        ->set('formCpf', '111.222.333-44')
        ->set('formBirthDate', '1985-01-01')
        ->set('formPhone', '(11) 88888-0000')
        ->call('save')
        ->assertHasErrors(['formCpf']);
});

test('patient search filters by name', function () {
    Patient::factory()->create(['name' => 'Carlos Busca']);
    Patient::factory()->create(['name' => 'Outro Nome']);

    Volt::test('clinica.patients')
        ->set('search', 'Carlos')
        ->assertSee('Carlos Busca')
        ->assertDontSee('Outro Nome');
});

// ── Appointments ──────────────────────────────────────────────────────────────

test('appointments page renders', function () {
    Volt::test('clinica.appointments')
        ->assertOk()
        ->assertSee('Agendamentos');
});

test('can create appointment via livewire component', function () {
    $patient = Patient::factory()->create();
    $doctor  = Doctor::factory()->create();

    Volt::test('clinica.appointments')
        ->call('openCreate')
        ->set('formPatientId', $patient->id)
        ->set('formDoctorId', $doctor->id)
        ->set('formScheduledAt', now()->addDay()->format('Y-m-d\TH:i'))
        ->set('formStatus', 'scheduled')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('appointments', [
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'status'     => 'scheduled',
    ]);
});

test('appointment filter by status shows only matching', function () {
    Patient::factory()->create();
    Doctor::factory()->create();

    $scheduled = Appointment::factory()->create(['status' => 'scheduled']);
    $cancelled = Appointment::factory()->create(['status' => 'cancelled']);

    Volt::test('clinica.appointments')
        ->set('filterStatus', 'scheduled')
        ->assertSee($scheduled->patient->name)
        ->assertDontSee($cancelled->patient->name);
});

// ── Rooms ─────────────────────────────────────────────────────────────────────

test('rooms page renders', function () {
    Volt::test('clinica.rooms')
        ->assertOk()
        ->assertSee('Salas');
});

test('can create room via livewire component', function () {
    Volt::test('clinica.rooms')
        ->call('openCreate')
        ->set('formName', 'Sala 101')
        ->set('formType', 'Consultório')
        ->set('formCapacity', '2')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('rooms', ['name' => 'Sala 101']);
});

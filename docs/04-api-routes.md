# Rotas da Aplicação

## Grupo A — Web Routes (Livewire + sessão)

Arquivo: `routes/web.php`  
Middleware base: `web`

### Autenticação (geradas pelo Breeze — `routes/auth.php`)

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/` | — | — | — | Redireciona para /login ou /dashboard |
| GET | `/login` | `Auth\Login` | `guest` | `login` | Formulário de login |
| POST | `/login` | `Auth\Login` | `guest` | — | Processar login |
| POST | `/logout` | — | `auth` | `logout` | Encerrar sessão |
| GET | `/register` | `Auth\Register` | `guest` | `register` | Registro de usuário (admin only em produção) |
| GET | `/forgot-password` | `Auth\ForgotPassword` | `guest` | `password.request` | Solicitar reset |
| POST | `/forgot-password` | `Auth\ForgotPassword` | `guest` | `password.email` | Enviar link de reset |
| GET | `/reset-password/{token}` | `Auth\ResetPassword` | `guest` | `password.reset` | Formulário de reset |
| POST | `/reset-password` | `Auth\ResetPassword` | `guest` | `password.store` | Processar reset |
| GET | `/verify-email` | `Auth\VerifyEmail` | `auth` | `verification.notice` | Aviso de verificação |
| GET | `/verify-email/{id}/{hash}` | — | `auth,signed,throttle:6,1` | `verification.verify` | Confirmar email |
| POST | `/email/verification-notification` | — | `auth,throttle:6,1` | `verification.send` | Reenviar email |
| GET | `/two-factor-challenge` | `Auth\TwoFactorChallenge` | `guest` | `two-factor.login` | Desafio 2FA |
| POST | `/two-factor-challenge` | `Auth\TwoFactorChallenge` | `guest` | — | Processar TOTP |

### Dashboard

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/dashboard` | `Dashboard\Index` | `auth,verified` | `dashboard` | Painel principal |

### Appointments

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/appointments` | `Appointments\AppointmentTable` | `auth,verified,can:view appointments` | `appointments.index` | Listagem |
| GET | `/appointments/create` | `Appointments\AppointmentForm` | `auth,verified,can:create appointments` | `appointments.create` | Formulário criação |
| GET | `/appointments/{appointment}` | `Appointments\AppointmentShow` | `auth,verified,can:view appointments` | `appointments.show` | Detalhes |
| GET | `/appointments/{appointment}/edit` | `Appointments\AppointmentForm` | `auth,verified,can:update appointments` | `appointments.edit` | Formulário edição |

### Doctors

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/doctors` | `Doctors\DoctorTable` | `auth,verified,can:view doctors` | `doctors.index` | Listagem |
| GET | `/doctors/create` | `Doctors\DoctorForm` | `auth,verified,can:manage doctors` | `doctors.create` | Formulário criação |
| GET | `/doctors/{doctor}` | `Doctors\DoctorShow` | `auth,verified,can:view doctors` | `doctors.show` | Perfil do médico |
| GET | `/doctors/{doctor}/edit` | `Doctors\DoctorForm` | `auth,verified,can:manage doctors` | `doctors.edit` | Formulário edição |

### Patients

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/patients` | `Patients\PatientTable` | `auth,verified,can:view patients` | `patients.index` | Listagem |
| GET | `/patients/create` | `Patients\PatientForm` | `auth,verified,can:create patients` | `patients.create` | Formulário criação |
| GET | `/patients/{patient}` | `Patients\PatientShow` | `auth,verified,can:view patients` | `patients.show` | Prontuário |
| GET | `/patients/{patient}/edit` | `Patients\PatientForm` | `auth,verified,can:update patients` | `patients.edit` | Formulário edição |

### Payments

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/payments` | `Payments\PaymentTable` | `auth,verified,can:view payments` | `payments.index` | Listagem de pagamentos |

### Expenses

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/expenses` | `Expenses\ExpenseTable` | `auth,verified,can:view expenses` | `expenses.index` | Relatório de despesas |

### Departments

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/departments` | `Departments\DepartmentTable` | `auth,verified,can:manage departments` | `departments.index` | Listagem |
| GET | `/departments/{department}` | `Departments\DepartmentShow` | `auth,verified,can:manage departments` | `departments.show` | Detalhes |

### Rooms

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/rooms` | `Rooms\RoomTable` | `auth,verified,can:manage rooms` | `rooms.index` | Alocação de salas |

### Insurance Companies

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/insurance` | `Insurance\InsuranceTable` | `auth,verified,can:manage insurance` | `insurance.index` | Listagem de convênios |

### Events

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/events` | `Events\EventCalendar` | `auth,verified` | `events.index` | Calendário de eventos |

### Chat

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/chat` | `Chat\ChatWindow` | `auth,verified` | `chat.index` | Chat geral |
| GET | `/chat/{user}` | `Chat\ChatWindow` | `auth,verified` | `chat.conversation` | Conversa específica |

### Profile

| Método | URI | Componente Livewire | Middleware | Named Route | Descrição |
|--------|-----|---------------------|-----------|------------|-----------|
| GET | `/profile` | `Profile\Edit` | `auth,verified` | `profile.edit` | Editar perfil |

---

## Grupo B — API Routes (Sanctum token, JSON)

Arquivo: `routes/api.php`  
Prefixo: `/api/v1`  
Content-Type: `application/json`  
Autenticação: Bearer token via Sanctum

### Auth

| Método | URI | Controller@method | Middleware | Request | Response |
|--------|-----|-------------------|-----------|---------|---------|
| POST | `/api/v1/auth/login` | `AuthController@login` | `throttle:5,1` | `LoginRequest` | `TokenResource` |
| POST | `/api/v1/auth/logout` | `AuthController@logout` | `auth:sanctum` | — | `204 No Content` |
| GET | `/api/v1/auth/me` | `AuthController@me` | `auth:sanctum` | — | `UserResource` |

#### POST /api/v1/auth/login

Request:
```json
{
    "email": "admin@clinica.dev",
    "password": "password",
    "device_name": "Navegador Chrome"
}
```

Response `200 OK`:
```json
{
    "data": {
        "token": "1|abc123...",
        "token_type": "Bearer",
        "user": {
            "id": "uuid-aqui",
            "name": "Administrador",
            "email": "admin@clinica.dev",
            "roles": ["admin"],
            "permissions": ["view appointments", "manage doctors"]
        }
    }
}
```

Response `422 Unprocessable Entity` (credenciais inválidas):
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["As credenciais fornecidas estão incorretas."]
    }
}
```

Response `429 Too Many Requests`:
```json
{
    "message": "Muitas tentativas de login. Aguarde 1 minuto.",
    "retry_after": 60
}
```

---

### Appointments API

| Método | URI | Controller@method | Middleware | Request | Response |
|--------|-----|-------------------|-----------|---------|---------|
| GET | `/api/v1/appointments` | `AppointmentController@index` | `auth:sanctum,can:view appointments` | Query params | `AppointmentCollection` |
| POST | `/api/v1/appointments` | `AppointmentController@store` | `auth:sanctum,can:create appointments` | `StoreAppointmentRequest` | `AppointmentResource 201` |
| GET | `/api/v1/appointments/{id}` | `AppointmentController@show` | `auth:sanctum,can:view appointments` | — | `AppointmentResource` |
| PATCH | `/api/v1/appointments/{id}` | `AppointmentController@update` | `auth:sanctum,can:update appointments` | `UpdateAppointmentRequest` | `AppointmentResource` |
| DELETE | `/api/v1/appointments/{id}` | `AppointmentController@destroy` | `auth:sanctum,can:cancel appointments` | — | `204 No Content` |

#### GET /api/v1/appointments — Query params

| Param | Tipo | Exemplo | Descrição |
|-------|------|---------|-----------|
| `date` | date | `2026-05-12` | Filtrar por data |
| `doctor_id` | uuid | `abc-123` | Filtrar por médico |
| `patient_id` | uuid | `abc-123` | Filtrar por paciente |
| `status` | string | `agendado` | Filtrar por status |
| `per_page` | int | `15` | Itens por página (máx: 100) |
| `page` | int | `1` | Página atual |

Response `200 OK`:
```json
{
    "data": [
        {
            "id": "uuid",
            "patient": {
                "id": "uuid",
                "name": "João Silva",
                "cpf": "000.000.000-00"
            },
            "doctor": {
                "id": "uuid",
                "name": "Dr. Roberto Lima",
                "specialty": "Cardiologia"
            },
            "scheduled_at": "2026-05-12T10:00:00-03:00",
            "duration_minutes": 30,
            "type": "consulta",
            "status": "agendado",
            "room": {
                "id": "uuid",
                "name": "Consultório 3"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 87,
        "last_page": 6
    }
}
```

#### POST /api/v1/appointments

Request:
```json
{
    "patient_id": "uuid-do-paciente",
    "doctor_id": "uuid-do-medico",
    "room_id": "uuid-da-sala",
    "scheduled_at": "2026-05-15T14:30:00",
    "duration_minutes": 30,
    "type": "consulta",
    "notes": "Retorno pós-exame de sangue"
}
```

Response `201 Created`:
```json
{
    "data": {
        "id": "novo-uuid",
        "status": "agendado",
        "scheduled_at": "2026-05-15T14:30:00-03:00",
        "patient": { "id": "uuid", "name": "João Silva" },
        "doctor": { "id": "uuid", "name": "Dr. Roberto Lima" },
        "created_at": "2026-05-12T13:00:00-03:00"
    }
}
```

Response `422 Unprocessable Entity`:
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "doctor_id": ["O médico não tem disponibilidade neste horário."],
        "scheduled_at": ["O agendamento deve ser para uma data futura."]
    }
}
```

---

### Patients API

| Método | URI | Controller@method | Middleware | Request | Response |
|--------|-----|-------------------|-----------|---------|---------|
| GET | `/api/v1/patients` | `PatientController@index` | `auth:sanctum,can:view patients` | Query: `q`, `per_page` | `PatientCollection` |
| POST | `/api/v1/patients` | `PatientController@store` | `auth:sanctum,can:create patients` | `StorePatientRequest` | `PatientResource 201` |
| GET | `/api/v1/patients/{id}` | `PatientController@show` | `auth:sanctum,can:view patients` | — | `PatientResource` |
| PATCH | `/api/v1/patients/{id}` | `PatientController@update` | `auth:sanctum,can:update patients` | `UpdatePatientRequest` | `PatientResource` |

#### GET /api/v1/patients?q=joao

Response `200 OK`:
```json
{
    "data": [
        {
            "id": "uuid",
            "name": "João Silva",
            "cpf": "000.000.000-00",
            "birthdate": "1985-03-20",
            "gender": "M",
            "phone": "(11) 99999-9999",
            "blood_type": "O+",
            "insurance": {
                "id": "uuid",
                "name": "Unimed"
            }
        }
    ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 3
    }
}
```

---

### Dashboard Stats API

| Método | URI | Controller@method | Middleware | Response |
|--------|-----|-------------------|-----------|---------|
| GET | `/api/v1/dashboard/stats` | `DashboardController@stats` | `auth:sanctum` | `StatsResource` |
| GET | `/api/v1/dashboard/chart` | `DashboardController@chart` | `auth:sanctum` | JSON |
| GET | `/api/v1/dashboard/doctor-on-duty` | `DashboardController@doctorOnDuty` | `auth:sanctum` | JSON |

#### GET /api/v1/dashboard/stats

Response `200 OK`:
```json
{
    "data": {
        "appointments": {
            "today": 40,
            "yesterday": 32,
            "delta_percent": 25.0
        },
        "new_admits": {
            "today": 21,
            "yesterday": 18,
            "delta_percent": 16.7
        },
        "operations": {
            "today": 14,
            "yesterday": 9,
            "delta_percent": 55.6
        },
        "doctors": {
            "available_today": 15,
            "total": 22
        },
        "nurses": {
            "available_today": 36,
            "total": 40
        },
        "earnings": {
            "today": 5214000,
            "yesterday": 4187600,
            "formatted": "R$ 52.140,00"
        },
        "cached_at": "2026-05-12T13:00:00-03:00"
    }
}
```

#### GET /api/v1/dashboard/chart?year=2026

Response `200 OK`:
```json
{
    "data": {
        "year": 2026,
        "labels": ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
        "datasets": {
            "general_patient": [120, 95, 140, 110, 165, 88, 0, 0, 0, 0, 0, 0],
            "opd": [45, 38, 62, 55, 78, 42, 0, 0, 0, 0, 0, 0]
        }
    }
}
```

---

### Doctors API

| Método | URI | Controller@method | Middleware | Request | Response |
|--------|-----|-------------------|-----------|---------|---------|
| GET | `/api/v1/doctors` | `DoctorController@index` | `auth:sanctum,can:view doctors` | Query: `specialty`, `available`, `per_page` | `DoctorCollection` |
| GET | `/api/v1/doctors/{id}/availability` | `DoctorController@availability` | `auth:sanctum` | Query: `date` | JSON |

#### GET /api/v1/doctors/{id}/availability?date=2026-05-15

Response `200 OK`:
```json
{
    "data": {
        "doctor_id": "uuid",
        "date": "2026-05-15",
        "available_slots": [
            "08:00", "08:30", "09:00", "09:30",
            "14:00", "14:30", "15:00"
        ],
        "booked_slots": [
            "10:00", "10:30", "11:00"
        ]
    }
}
```

---

### Notifications API

| Método | URI | Controller@method | Middleware | Response |
|--------|-----|-------------------|-----------|---------|
| GET | `/api/v1/notifications` | `NotificationController@index` | `auth:sanctum` | JSON |
| PATCH | `/api/v1/notifications/{id}/read` | `NotificationController@markRead` | `auth:sanctum` | `204` |
| POST | `/api/v1/notifications/read-all` | `NotificationController@markAllRead` | `auth:sanctum` | `204` |

#### GET /api/v1/notifications

Response `200 OK`:
```json
{
    "data": [
        {
            "id": "uuid",
            "type": "App\\Notifications\\AppointmentConfirmed",
            "title": "Consulta confirmada",
            "body": "Sua consulta com Dr. Roberto Lima foi confirmada para amanhã às 10h.",
            "read_at": null,
            "created_at": "2026-05-12T12:00:00-03:00"
        }
    ],
    "unread_count": 3
}
```

---

## Rate Limiting por grupo

```php
// bootstrap/app.php ou AppServiceProvider

RateLimiter::for('login', function () {
    return Limit::perMinute(5)->by(request()->ip());
});

RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('stats', function (Request $request) {
    // Stats endpoint tem cache próprio, rate limit alto
    return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
});
```

---

## Named Routes — Referência rápida

```php
// Auth
route('login')                            // /login
route('logout')                           // POST /logout
route('password.request')                 // /forgot-password
route('verification.notice')              // /verify-email

// App
route('dashboard')                        // /dashboard
route('appointments.index')               // /appointments
route('appointments.create')              // /appointments/create
route('appointments.show', $appt)        // /appointments/{id}
route('appointments.edit', $appt)        // /appointments/{id}/edit
route('doctors.index')                    // /doctors
route('doctors.show', $doctor)           // /doctors/{id}
route('patients.index')                   // /patients
route('patients.show', $patient)         // /patients/{id}
route('payments.index')                   // /payments
route('expenses.index')                   // /expenses
route('departments.index')                // /departments
route('rooms.index')                      // /rooms
route('insurance.index')                  // /insurance
route('events.index')                     // /events
route('chat.index')                       // /chat
route('chat.conversation', $user)        // /chat/{user}
route('profile.edit')                     // /profile
```

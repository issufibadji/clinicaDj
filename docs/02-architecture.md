# Arquitetura do Sistema

## 2.1 Decisões de arquitetura e justificativas

### Por que Livewire 3 (em vez de Vue/React/Inertia)?

Livewire 3 foi escolhido porque a equipe de desenvolvimento tem proficiência sólida em PHP/Laravel e o projeto não exige interatividade tão granular que justifique um SPA completo. Com Livewire, a lógica de negócio permanece 100% no servidor, o código é testável com as ferramentas Laravel nativas (Pest), e não há necessidade de manter dois contextos de estado (frontend + backend). O Livewire 3 introduziu Volt (componentes inline), lazy loading de componentes e melhorias significativas de performance com o wire:model deferido. Inertia.js foi descartado porque exigiria Vue/React para toda a UI, aumentando a complexidade do build e a superfície de bugs de hidratação.

### Por que Alpine.js junto com Livewire?

Alpine.js resolve interações puramente visuais que não precisam ir ao servidor: abrir/fechar dropdowns, toggles de dark mode, transições de modais, estado do sidebar. Usar Livewire para isso geraria round-trips desnecessários. A sinergia Alpine + Livewire é o padrão oficial recomendado pela equipe Livewire e está documentada extensamente. Alpine é incluído automaticamente pelo Livewire 3 — não há configuração adicional.

### Por que Tailwind CSS?

Tailwind CSS garante consistência visual através de um design system baseado em tokens (cores, espaçamentos, tipografia definidos em `tailwind.config.js`). Elimina conflitos de CSS global, facilita dark mode via classe `dark:`, e o PurgeCSS integrado garante bundles mínimos em produção. A ausência de classes semânticas genéricas força o desenvolvedor a ser explícito sobre o design, reduzindo surpresas.

### Estratégia de módulos

Optamos pela **estrutura convencional Laravel** (sem módulos HMVC ou pacotes como nwidart/laravel-modules) para o MVP. A justificativa: a clínica tem escopo bem definido, a equipe é pequena (1–3 desenvolvedores), e módulos adicionam overhead de configuração. Se o projeto crescer para multi-tenant ou múltiplos produtos, a migração para módulos pode ser feita incrementalmente. Os Livewire components funcionam como a camada de "módulos de UI" naturalmente.

---

## 2.2 Estrutura de pastas completa

```
app-clinica-jm/
├── app/
│   ├── Actions/                    # Lógica de negócio isolada e testável
│   │   ├── Appointments/
│   │   │   ├── CreateAppointmentAction.php
│   │   │   ├── UpdateAppointmentAction.php
│   │   │   └── CancelAppointmentAction.php
│   │   ├── Doctors/
│   │   │   ├── CreateDoctorAction.php
│   │   │   └── UpdateDoctorAction.php
│   │   ├── Patients/
│   │   │   ├── CreatePatientAction.php
│   │   │   └── UpdatePatientAction.php
│   │   └── Payments/
│   │       └── ProcessPaymentAction.php
│   ├── Events/                     # Eventos do domínio
│   │   ├── AppointmentCreated.php
│   │   ├── AppointmentCancelled.php
│   │   └── PaymentProcessed.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PushSubscriptionController.php
│   │   │   └── Api/
│   │   │       ├── V1/
│   │   │       │   ├── AuthController.php
│   │   │       │   ├── AppointmentController.php
│   │   │       │   └── PatientController.php
│   │   ├── Middleware/
│   │   │   ├── CheckPermission.php
│   │   │   └── EnsureUserIsActive.php
│   │   └── Requests/               # Form Requests para toda validação
│   │       ├── Appointments/
│   │       │   ├── StoreAppointmentRequest.php
│   │       │   └── UpdateAppointmentRequest.php
│   │       ├── Doctors/
│   │       ├── Patients/
│   │       └── Auth/
│   │           └── LoginRequest.php
│   ├── Listeners/                  # Handlers de eventos
│   │   ├── SendAppointmentConfirmation.php
│   │   ├── NotifyDoctorOnAppointment.php
│   │   └── AuditPaymentProcessed.php
│   ├── Livewire/                   # Componentes Livewire (view-controllers)
│   │   ├── Dashboard/
│   │   │   ├── StatsCards.php
│   │   │   ├── DoctorOnDuty.php
│   │   │   ├── SurveyChart.php
│   │   │   └── MiniCalendar.php
│   │   ├── Appointments/
│   │   │   ├── AppointmentTable.php
│   │   │   ├── AppointmentForm.php
│   │   │   └── AppointmentModal.php
│   │   ├── Doctors/
│   │   │   ├── DoctorTable.php
│   │   │   └── DoctorForm.php
│   │   ├── Patients/
│   │   │   ├── PatientTable.php
│   │   │   └── PatientForm.php
│   │   ├── Payments/
│   │   │   └── PaymentModal.php
│   │   ├── Chat/
│   │   │   └── ChatWindow.php
│   │   └── Shared/
│   │       ├── NotificationBell.php
│   │       ├── Sidebar.php
│   │       └── DarkModeToggle.php
│   ├── Models/
│   │   ├── User.php                    # Notifiable trait + pushSubscriptions() + pushNotify()
│   │   ├── Doctor.php
│   │   ├── Patient.php
│   │   ├── Appointment.php
│   │   ├── Room.php
│   │   ├── Payment.php
│   │   ├── Expense.php
│   │   ├── Department.php
│   │   ├── InsuranceCompany.php
│   │   ├── Event.php
│   │   ├── Message.php
│   │   ├── MenuItem.php
│   │   ├── SystemSetting.php
│   │   └── PushSubscription.php
│   ├── Notifications/              # Laravel Notifications (canal database)
│   │   ├── AppointmentCreatedNotification.php
│   │   ├── AppointmentStatusChangedNotification.php
│   │   ├── NewPaymentNotification.php
│   │   └── ManualNotification.php      # batch_id + webPushPayload() helper
│   ├── Policies/                   # Autorização por modelo
│   │   ├── AppointmentPolicy.php
│   │   ├── DoctorPolicy.php
│   │   └── PatientPolicy.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── EventServiceProvider.php
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── permission.php              # Spatie config
│   ├── audit.php                   # owen-it config
│   └── clinica.php                 # Configs customizadas da clínica
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── DoctorFactory.php
│   │   ├── PatientFactory.php
│   │   └── AppointmentFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2024_01_01_000010_create_doctors_table.php
│   │   ├── 2024_01_01_000020_create_patients_table.php
│   │   ├── 2024_01_01_000030_create_rooms_table.php
│   │   ├── 2024_01_01_000040_create_departments_table.php
│   │   ├── 2024_01_01_000050_create_insurance_companies_table.php
│   │   ├── 2024_01_01_000060_create_appointments_table.php
│   │   ├── 2024_01_01_000070_create_payments_table.php
│   │   ├── 2024_01_01_000080_create_expenses_table.php
│   │   ├── 2024_01_01_000090_create_events_table.php
│   │   ├── 2024_01_01_000100_create_messages_table.php
│   │   └── 2024_01_01_000110_create_notifications_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       ├── UserSeeder.php
│       ├── DoctorSeeder.php
│       ├── PatientSeeder.php
│       └── AppointmentSeeder.php
├── docs/                           # Esta pasta
├── resources/
│   ├── css/
│   │   └── app.css                 # @tailwind directives + variáveis CSS
│   ├── js/
│   │   ├── app.js                  # Bootstrap Livewire + Alpine + Chart.js
│   │   └── charts/
│   │       └── survey-chart.js     # Configuração Chart.js
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php       # Layout principal (sidebar + topbar)
│       │   └── auth.blade.php      # Layout de autenticação
│       ├── livewire/
│       │   ├── dashboard/
│       │   │   ├── stats-cards.blade.php
│       │   │   ├── doctor-on-duty.blade.php
│       │   │   ├── appointment-chart.blade.php
│       │   │   └── mini-calendar.blade.php
│       │   ├── clinica/
│       │   │   ├── appointments.blade.php
│       │   │   ├── doctors.blade.php
│       │   │   ├── patients.blade.php
│       │   │   ├── payments.blade.php
│       │   │   ├── expenses.blade.php
│       │   │   ├── rooms.blade.php
│       │   │   ├── departments.blade.php
│       │   │   ├── insurance.blade.php
│       │   │   ├── events.blade.php
│       │   │   ├── chat.blade.php
│       │   │   └── notifications.blade.php     # página /notificacoes
│       │   ├── notifications/
│       │   │   └── notification-panel.blade.php  # sino na topbar (wire:poll.30s)
│       │   ├── admin/
│       │   │   └── notifications/
│       │   │       └── notification-manager.blade.php
│       │   └── layout/
│       │       └── navigation.blade.php
│       ├── partials/
│       │   ├── sidebar.blade.php
│       │   ├── topbar.blade.php        # inclui <livewire:notifications.notification-panel>
│       │   └── flash.blade.php
│       └── components/             # Blade components reutilizáveis
│           ├── card.blade.php
│           ├── kpi-card.blade.php
│           ├── badge.blade.php
│           ├── modal.blade.php
│           └── alert.blade.php
├── public/
│   ├── index.php
│   ├── service-worker.js           # Web Push: recebe push event, abre URL no click
│   └── build/                      # Assets compilados pelo Vite
├── routes/
│   ├── web.php                     # Dashboard, perfil, push-subscriptions
│   ├── admin.php                   # Rotas admin (RBAC, notificações, sistema)
│   ├── modules.php                 # Módulos clínicos + notificações
│   ├── api.php                     # Rotas API (Sanctum)
│   ├── profile.php                 # Perfil/conta Breeze
│   └── auth.php                    # Rotas Breeze (login, 2FA, etc.)
├── storage/
│   ├── app/
│   │   └── public/                 # Avatares, documentos
│   └── logs/
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   └── LoginTest.php
│   │   ├── Appointments/
│   │   │   └── AppointmentCrudTest.php
│   │   └── Dashboard/
│   │       └── DashboardTest.php
│   ├── Unit/
│   │   ├── Actions/
│   │   │   └── CreateAppointmentActionTest.php
│   │   └── Models/
│   │       └── AppointmentTest.php
│   └── Pest.php
├── .env.example
├── .pint.json
├── artisan
├── composer.json
├── package.json
├── phpstan.neon
├── tailwind.config.js
└── vite.config.js
```

---

## 2.3 Padrões de código

### Camadas da aplicação

```
HTTP Request
    │
    ▼
FormRequest (validação + autorização)
    │
    ▼
Controller (API) ou Livewire Component (Web)
    │
    ▼
Action (lógica de negócio, 1 responsabilidade)
    │
    ├──► Model (Eloquent, queries, relacionamentos)
    │
    └──► Event (disparado após a ação)
              │
              ▼
          Listener (side-effects: email, log, cache bust)
```

**Regras:**
- Controllers são finos: recebem request, chamam Action, retornam Response/Resource
- Actions nunca recebem `Request` diretamente — apenas DTOs ou valores primitivos
- Nenhum Repository pattern no MVP (Eloquent é suficiente para a escala atual)
- Queries complexas ficam em **Scopes** nos Models ou em classes `Query` dedicadas

### Form Requests
Todo endpoint que recebe dados usa um FormRequest próprio. A autorização via `authorize()` verifica permissão Spatie antes de atingir o Controller.

```php
// Exemplo
public function authorize(): bool
{
    return $this->user()->can('create appointments');
}

public function rules(): array
{
    return [
        'patient_id' => ['required', 'uuid', 'exists:patients,id'],
        'doctor_id'  => ['required', 'uuid', 'exists:doctors,id'],
        'scheduled_at' => ['required', 'date', 'after:now'],
    ];
}
```

### API Resources
Toda resposta JSON usa um Resource para garantir shape consistente e esconder campos internos:

```php
// app/Http/Resources/AppointmentResource.php
public function toArray(Request $request): array
{
    return [
        'id'           => $this->id,
        'patient'      => PatientResource::make($this->patient),
        'scheduled_at' => $this->scheduled_at->toIso8601String(),
        'status'       => $this->status,
    ];
}
```

### Events e Listeners

| Evento | Listener(s) |
|--------|-------------|
| `AppointmentCreated` | `SendAppointmentConfirmation`, `NotifyDoctorOnAppointment` |
| `AppointmentCancelled` | `SendCancellationEmail`, `ReleaseRoomSlot` |
| `PaymentProcessed` | `AuditPaymentProcessed`, `UpdateEarningsCache` |

---

## 2.4 Fluxo de autenticação

```
                    ┌─────────┐
      Visitante ───►│  /login │
                    └────┬────┘
                         │ POST email + password
                         ▼
                    ┌────────────────────┐
                    │  Rate limit ok?    │
                    └────────┬───────────┘
                       Não   │  Sim
                         ▼   ▼
                    429  ┌───────────────────┐
                    Too  │ Credenciais válidas?│
                    Many └──────┬────────────┘
                          Não   │  Sim
                            ▼   ▼
                         401  ┌──────────────────┐
                         Unauth│ Email verificado? │
                               └───────┬──────────┘
                                 Não   │  Sim
                                   ▼   ▼
                              /email  ┌────────────────┐
                              /verify │ 2FA habilitado? │
                                      └──────┬─────────┘
                                       Não   │  Sim
                                         ▼   ▼
                                    ┌───────────────────┐
                                    │ /two-factor-challenge│
                                    └──────┬────────────┘
                                           │ TOTP válido
                                           ▼
                                    ┌──────────────┐
                                    │  /dashboard  │
                                    └──────────────┘
```

---

## 2.5 Estratégia de testes

### Testes unitários (Unit)
- Testam **Actions** isoladamente com dados mockados
- Testam **Models**: scopes, relacionamentos, métodos de negócio
- Testam **Helpers** e **Services** utilitários
- Não sobem HTTP, não usam banco real (use factories + fakes)

### Testes de feature (Feature)
- Testam endpoints HTTP da API (`/api/v1/*`) com banco SQLite em memória
- Testam componentes **Livewire** com `Livewire::test()`
- Verificam middleware (auth, permission), form validation, redirects
- Usam factories para criar estado inicial

### Testes de browser (E2E — opcional no MVP)
- Pest com Laravel Dusk para fluxos críticos:
  - Login completo com 2FA
  - Criar agendamento end-to-end
  - Fluxo de pagamento
- Rodam somente no CI com headless Chrome

### Convenções Pest

```php
// tests/Feature/Appointments/AppointmentCrudTest.php
it('allows recepcionista to create appointment', function () {
    $user = User::factory()->recepcionista()->create();
    $patient = Patient::factory()->create();
    $doctor = Doctor::factory()->create();

    Livewire::actingAs($user)
        ->test(AppointmentForm::class)
        ->set('patient_id', $patient->id)
        ->set('doctor_id', $doctor->id)
        ->set('scheduled_at', now()->addDay()->format('Y-m-d H:i'))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('appointment-saved');
});
```

---

## 2.6 Middleware stack (ordem exata)

### Web routes (sessão)

```
web
 └── StartSession
 └── VerifyCsrfToken (automático Livewire)
 └── ShareErrorsFromSession
      └── auth          (RedirectIfNotAuthenticated)
           └── verified (EnsureEmailIsVerified)
                └── CheckPermission (Spatie gate check por rota)
```

### API routes (Sanctum)

```
api
 └── ThrottleRequests:api
 └── SubstituteBindings
      └── auth:sanctum  (token validation)
           └── CheckPermission
```

### Middleware customizado: `CheckPermission`

```php
// app/Http/Middleware/CheckPermission.php
public function handle(Request $request, Closure $next, string ...$permissions): Response
{
    if (!$request->user()?->hasAnyPermission($permissions)) {
        abort(403, 'Acesso não autorizado.');
    }
    return $next($request);
}
```

---

## 2.7 Fluxo de dados Livewire

### Comunicação entre componentes

```
Parent Component
    │
    │── dispatch('event-name', ['payload'])
    │
    ▼
Child Component  (ouve via #[On('event-name')])
    │
    │── $this->dispatch('event-name')  ← para subir ao parent
    │── $this->dispatch('event-name')->to(OtherComponent::class)
```

**Regra:** Componentes irmãos nunca se comunicam diretamente. Usam eventos globais via `dispatch()` sem target.

### Quando usar cada tipo de `wire:model`

| Diretiva | Quando usar | Exemplo |
|----------|-------------|---------|
| `wire:model` | Formulários simples sem feedback imediato | campos de cadastro |
| `wire:model.live` | Busca/filtro em tempo real, feedback instantâneo | campo de busca em tabelas |
| `wire:model.blur` | Validação de campo individual ao sair do foco | email, CPF, CRM |
| `wire:model.lazy` | Formulários longos onde sincronização a cada tecla é cara | textarea de notas clínicas |

### Polling para Chat e Notificações

```blade
{{-- Chat: atualiza a cada 3 segundos --}}
<div wire:poll.3s="loadMessages">
    @foreach($messages as $message)
        ...
    @endforeach
</div>

{{-- NotificationBell: atualiza a cada 30 segundos --}}
<div wire:poll.30s="loadNotifications">
    ...
</div>
```

O polling é pausado automaticamente pelo Livewire quando a aba está inativa (Livewire 3 usa Page Visibility API).

---

## 2.8 Segurança

### CSRF
Automático no Livewire 3. Todas as requests Livewire incluem o token CSRF no header `X-CSRF-TOKEN`. Não é necessária nenhuma configuração adicional.

### Proteção XSS
- Blade `{{ $var }}` escapa HTML automaticamente — usar `{!! !!}` apenas quando o conteúdo é HTML confiável e necessário
- Alpine.js usa `x-text` (escapa) por padrão; `x-html` é evitado exceto em conteúdo sanitizado

### SQL Injection
- Eloquent ORM usa prepared statements em todas as queries
- Query Builder com bindings (`where('column', $value)`) — nunca interpolação de strings
- Proibido: `DB::statement("SELECT * WHERE id = $id")`

### Rate limiting nas rotas de auth

```php
// routes/web.php
Route::middleware(['throttle:login'])->group(function () {
    Route::post('/login', LoginController::class);
});

// app/Providers/AppServiceProvider.php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip())
        ->response(fn() => response()->json([
            'message' => 'Muitas tentativas. Aguarde 1 minuto.'
        ], 429));
});
```

### Headers de segurança

```php
// app/Http/Middleware/SecurityHeaders.php (adicionado via AppServiceProvider)
'X-Frame-Options'        => 'DENY',
'X-Content-Type-Options' => 'nosniff',
'Referrer-Policy'        => 'strict-origin-when-cross-origin',
'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
```

### Auditoria de ações sensíveis

Owen-it/laravel-auditing é configurado nos models `Appointment`, `Patient`, `Payment` e `User`. Registra automaticamente `created`, `updated`, `deleted` com o `user_id` do agente, IP e user-agent.

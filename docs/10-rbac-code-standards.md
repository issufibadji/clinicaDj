# Padrões de Código e Templates — app-clinica-jm

Referência consultada antes de criar qualquer arquivo PHP, Blade ou JS no projeto.
Complementa o [rbac-implementation-plan.md](rbac-implementation-plan.md).

---

## Seção 1 — Convenções de nomenclatura

### 1.1 — Models

- PascalCase singular: `Doctor`, `Appointment`, `InsuranceCompany`
- Tabela inferida automaticamente (plural snake_case): `insurance_companies`
- UUID como PK: usar trait `HasUuids` do Laravel 12 em todos os models
- Soft delete obrigatório em: `User`, `Doctor`, `Patient`, `Appointment`
- `Auditable` obrigatório em: `User`, `Doctor`, `Patient`, `Appointment`, `Payment`, `Role`, `Permission`, `MenuItem`, `SystemSetting`

### 1.2 — Migrations

| Operação | Padrão |
|----------|--------|
| Criar tabela | `create_tabela_table` |
| Adicionar coluna | `add_coluna_to_tabela_table` |
| Alterar coluna | `modify_coluna_in_tabela_table` |

### 1.3 — Actions

- PascalCase com verbo + substantivo: `CreateUser`, `UpdatePassword`, `SyncUserRoles`
- Namespace por domínio: `App\Actions\{Dominio}\{Action}`
- Domínios válidos: `Users`, `Roles`, `Permissions`, `Appointments`, `Patients`, `Doctors`, `Payments`, `System`, `Profile`, `Auth`
- Método único público: `handle()`
- Não fazem validação de input (responsabilidade do Form Request ou Livewire)

### 1.4 — Livewire Components

- PascalCase por domínio: `AccessControl\UserTable`, `System\AuditTable`
- Um componente por responsabilidade: `Table`, `Form`, `Modal` em arquivos separados
- Namespace base: `App\Livewire\{Grupo}\{Componente}`

### 1.5 — Rotas nomeadas

Padrão: `grupo.recurso.acao`

```
admin.usuarios.index
admin.usuarios.create
admin.usuarios.edit
admin.papeis.index
admin.papeis.create
admin.papeis.edit
admin.permissoes.index
admin.permissoes.create
admin.vinculo.index
admin.sistema.auditoria
admin.sistema.menus
admin.sistema.configuracoes
profile.show
profile.settings
two-factor.challenge
dashboard
```

### 1.6 — Permissões Spatie

Padrão obrigatório: `modulo.acao` (lowercase com ponto)

**Módulos válidos:**
`appointments`, `patients`, `doctors`, `payments`, `reports`, `rooms`, `departments`, `insurance`, `events`, `chat`, `users`, `roles`, `permissions`, `system`

**Ações válidas:**
`view`, `create`, `edit`, `delete`, `manage`, `export`, `audit`, `menus`, `settings`

**Exemplos corretos:**
```
appointments.view    ✅
patients.create      ✅
system.audit         ✅
Users.View           ❌  (não usar maiúsculas)
viewUsers            ❌  (não usar camelCase)
```

### 1.7 — Events e Listeners

- Event: `UserCreated`, `AppointmentScheduled`, `TwoFactorEnabled`
- Listener: `SendWelcomeEmail`, `NotifyDoctorOfAppointment`, `LogSecurityEvent`
- Namespace: `App\Events\` e `App\Listeners\`

### 1.8 — Form Requests

Padrão: `Verbo` + `Recurso` + `Request`

```
CreateUserRequest
UpdateUserRequest
UpdatePasswordRequest
StoreAppointmentRequest
UpdateAppointmentRequest
```

Namespace: `App\Http\Requests\{Grupo}\{Request}`

### 1.9 — Policies

- Padrão: `{Recurso}Policy` — `UserPolicy`, `RolePolicy`, `AppointmentPolicy`
- Métodos padrão: `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`
- Métodos extras permitidos para lógica específica: `toggleStatus`, `resetPassword`, `export`

---

## Seção 2 — Templates de código

### 2.1 — Model base com todas as traits

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Appointment extends Model implements AuditableContract
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'room_id',
        'scheduled_at',
        'duration_minutes',
        'type',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_at'     => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // Campos sensíveis excluídos da auditoria
    protected $auditExclude = [];

    // ── Relacionamentos ───────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
```

### 2.2 — Action padrão

```php
<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function handle(array $data): User
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'phone'     => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user;
    }
}
```

### 2.3 — Form Request com regras de autorização

```php
<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[a-z]/', 'regex:/[0-9]/'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['exists:roles,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Selecione ao menos um papel para o usuário.',
            'password.regex' => 'A senha deve conter letras e números.',
        ];
    }
}
```

### 2.4 — Policy completa

```php
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $auth): bool
    {
        return $auth->can('users.view');
    }

    public function view(User $auth, User $user): bool
    {
        return $auth->can('users.view');
    }

    public function create(User $auth): bool
    {
        return $auth->can('users.create');
    }

    public function update(User $auth, User $user): bool
    {
        // Admin só pode ser editado por outro admin
        if ($user->hasRole('admin') && ! $auth->hasRole('admin')) {
            return false;
        }

        return $auth->can('users.edit');
    }

    public function delete(User $auth, User $user): bool
    {
        // Não pode excluir a si mesmo
        if ($auth->id === $user->id) {
            return false;
        }

        // Admin não pode ser excluído por ninguém
        if ($user->hasRole('admin')) {
            return false;
        }

        return $auth->can('users.delete');
    }

    public function restore(User $auth, User $user): bool
    {
        return $auth->hasRole('admin');
    }
}
```

### 2.5 — Livewire Table component completo

```php
<?php

namespace App\Livewire\AccessControl;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $roleFilter = '';

    #[Url]
    public string $statusFilter = '';

    public bool $showForm = false;
    public ?string $editingId = null;

    // Resetar paginação ao filtrar
    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedRoleFilter(): void    { $this->resetPage(); }
    public function updatedStatusFilter(): void  { $this->resetPage(); }

    #[Computed]
    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->when($this->roleFilter, fn ($q) =>
                $q->whereHas('roles', fn ($r) => $r->where('name', $this->roleFilter))
            )
            ->when($this->statusFilter !== '', fn ($q) =>
                $q->where('is_active', $this->statusFilter === 'active')
            )
            ->latest()
            ->paginate(15);
    }

    public function openCreate(): void
    {
        $this->authorize('create', User::class);
        $this->editingId = null;
        $this->showForm  = true;
    }

    public function openEdit(string $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $this->editingId = $id;
        $this->showForm  = true;
    }

    public function suspend(string $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $user->update(['is_active' => ! $user->is_active]);
        $this->dispatch('flash', message: $user->is_active ? 'Usuário ativado.' : 'Usuário suspenso.');
    }

    public function delete(string $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $user->delete();
        $this->dispatch('flash', message: 'Usuário removido.');
    }

    public function exportJson(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('viewAny', User::class);

        return response()->streamDownload(function () {
            echo $this->users->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, 'usuarios-' . now()->format('Y-m-d') . '.json');
    }

    #[On('user-saved')]
    public function onUserSaved(): void
    {
        $this->showForm  = false;
        $this->editingId = null;
        $this->dispatch('flash', message: 'Usuário salvo com sucesso.');
    }

    public function render()
    {
        return view('livewire.access-control.user-table');
    }
}
```

### 2.6 — Livewire Form component com upload

```php
<?php

namespace App\Livewire\AccessControl;

use App\Actions\Users\CreateUser;
use App\Actions\Users\UpdateUser;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    use WithFileUploads;

    public ?string $userId = null;

    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $phone    = '';
    public array  $selectedRoles = [];
    public bool   $isActive = true;
    public $avatar = null;

    public function mount(?string $userId = null): void
    {
        $this->userId = $userId;

        if ($userId) {
            $user = User::with('roles')->findOrFail($userId);
            $this->name          = $user->name;
            $this->email         = $user->email;
            $this->phone         = $user->phone ?? '';
            $this->isActive      = $user->is_active;
            $this->selectedRoles = $user->roles->pluck('name')->toArray();
        }
    }

    protected function rules(): array
    {
        $passwordRules = $this->userId
            ? ['nullable', 'string', 'min:8']
            : ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[0-9]/'];

        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email,' . $this->userId],
            'password'      => $passwordRules,
            'phone'         => ['nullable', 'string', 'max:20'],
            'selectedRoles' => ['required', 'array', 'min:1'],
            'avatar'        => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'is_active' => $this->isActive,
            'roles'     => $this->selectedRoles,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->storePublicly('avatars', 'public');
        }

        if ($this->userId) {
            $this->authorize('update', User::findOrFail($this->userId));
            app(UpdateUser::class)->handle($this->userId, $data);
        } else {
            $this->authorize('create', User::class);
            app(CreateUser::class)->handle($data);
        }

        $this->dispatch('user-saved');
    }

    public function render()
    {
        return view('livewire.access-control.user-form', [
            'availableRoles' => \Spatie\Permission\Models\Role::orderBy('level')->get(),
        ]);
    }
}
```

### 2.7 — Componente Sistema (Settings inline)

```php
<?php

namespace App\Livewire\System;

use App\Actions\Admin\System\SaveSystemSettingAction;
use App\Models\SystemSetting;
use Livewire\Component;

class SettingsTable extends Component
{
    public bool   $showFlash    = false;
    public string $flashMessage = '';

    public function mount(): void
    {
        $this->authorize('system.settings');
    }

    public function updateSetting(string $key, mixed $value): void
    {
        $this->authorize('system.settings');
        app(SaveSystemSettingAction::class)->execute($key, $value);
        $this->flashMessage = 'Salvo ✓';
        $this->showFlash    = true;
        // Auto-dismiss via Alpine no template
    }

    public function render()
    {
        return view('livewire.system.settings-table', [
            'settings' => SystemSetting::orderBy('id')->get(),
        ]);
    }
}
```

### 2.8 — Componente Auditoria com filtros e URL params

```php
<?php

namespace App\Livewire\System;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use OwenIt\Auditing\Models\Audit;

class AuditTable extends Component
{
    use WithPagination;

    #[Url] public string $entity = '';
    #[Url] public string $action = '';
    #[Url] public string $from   = '';
    #[Url] public string $to     = '';

    public ?array $diffData = null;

    public function mount(): void
    {
        $this->authorize('system.audit');
    }

    public function filter(): void      { $this->resetPage(); }
    public function clearFilters(): void
    {
        $this->reset(['entity', 'action', 'from', 'to']);
        $this->resetPage();
    }

    public function openDiff(int $auditId): void
    {
        $audit = Audit::findOrFail($auditId);
        $this->diffData = [
            'old'   => $audit->old_values,
            'new'   => $audit->new_values,
            'event' => $audit->event,
            'user'  => $audit->user?->name ?? 'Sistema',
            'date'  => $audit->created_at->format('d/m/Y H:i:s'),
        ];
        $this->dispatch('open-diff-modal');
    }

    public function exportJson(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('system.audit');
        $data = $this->buildQuery()->get();

        return response()->streamDownload(function () use ($data) {
            echo $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, 'auditoria-' . now()->format('Y-m-d') . '.json');
    }

    private function buildQuery()
    {
        return Audit::with('user')
            ->when($this->entity, fn ($q) =>
                $q->where('auditable_type', 'App\\Models\\' . $this->entity)
            )
            ->when($this->action, fn ($q) =>
                $q->where('event', $this->action)
            )
            ->when($this->from, fn ($q) =>
                $q->whereDate('created_at', '>=', $this->from)
            )
            ->when($this->to, fn ($q) =>
                $q->whereDate('created_at', '<=', $this->to)
            )
            ->latest();
    }

    public function render()
    {
        return view('livewire.system.audit-table', [
            'audits'   => $this->buildQuery()->paginate(15),
            'entities' => $this->getAuditableEntities(),
        ]);
    }

    private function getAuditableEntities(): array
    {
        return Audit::distinct()
            ->pluck('auditable_type')
            ->map(fn ($t) => class_basename($t))
            ->sort()
            ->values()
            ->toArray();
    }
}
```

### 2.9 — MenuManager (toggle e dropdown inline)

```php
<?php

namespace App\Livewire\System;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class MenuManager extends Component
{
    public function mount(): void
    {
        $this->authorize('system.menus');
    }

    public function toggleVisibility(string $id): void
    {
        $item = MenuItem::findOrFail($id);
        $item->update(['is_visible' => ! $item->is_visible]);
        $this->invalidateMenuCache();
        $this->dispatch('flash', message: 'Visibilidade atualizada.');
    }

    public function updateMinLevel(string $id, int $level): void
    {
        MenuItem::findOrFail($id)->update(['min_level' => $level]);
        $this->invalidateMenuCache();
        $this->dispatch('flash', message: 'Nível mínimo atualizado.');
    }

    private function invalidateMenuCache(): void
    {
        foreach (range(1, 4) as $level) {
            Cache::forget("sidebar.menu.level.{$level}");
        }
    }

    public function render()
    {
        return view('livewire.system.menu-manager', [
            'menus'  => MenuItem::orderBy('group')->orderBy('order')->get(),
            'levels' => Role::orderBy('level')->get(['name', 'level']),
        ]);
    }
}
```

### 2.10 — Teste Pest para componente Livewire

```php
<?php

use App\Livewire\AccessControl\UserTable;
use App\Models\User;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('renders user table for admin', function () {
    Livewire::actingAs($this->admin)
        ->test(UserTable::class)
        ->assertOk()
        ->assertSee('Usuários');
});

it('filters by search', function () {
    User::factory()->create(['name' => 'Dr. João Silva',  'email' => 'joao@clinica.com']);
    User::factory()->create(['name' => 'Maria Santos',    'email' => 'maria@clinica.com']);

    Livewire::actingAs($this->admin)
        ->test(UserTable::class)
        ->set('search', 'João')
        ->assertSee('joao@clinica.com')
        ->assertDontSee('maria@clinica.com');
});

it('denies access to recepcionista', function () {
    $recep = User::factory()->create();
    $recep->assignRole('recepcionista');

    Livewire::actingAs($recep)
        ->test(UserTable::class)
        ->assertForbidden();
});

it('cannot delete own account', function () {
    Livewire::actingAs($this->admin)
        ->test(UserTable::class)
        ->call('delete', $this->admin->id)
        ->assertForbidden();
});

it('soft deletes user', function () {
    $user = User::factory()->create();
    $user->assignRole('recepcionista');

    Livewire::actingAs($this->admin)
        ->test(UserTable::class)
        ->call('delete', $user->id)
        ->assertDispatched('flash');

    expect(User::withTrashed()->find($user->id)->deleted_at)->not->toBeNull();
});
```

### 2.11 — Teste Pest para Action

```php
<?php

use App\Actions\Users\CreateUser;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates user with role', function () {
    $user = app(CreateUser::class)->handle([
        'name'     => 'Dr. Teste',
        'email'    => 'teste@clinica.com',
        'password' => 'senha123',
        'roles'    => ['medico'],
    ]);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->email)->toBe('teste@clinica.com')
        ->and($user->hasRole('medico'))->toBeTrue()
        ->and($user->is_active)->toBeTrue();
});

it('throws on duplicate email', function () {
    User::factory()->create(['email' => 'duplicado@clinica.com']);

    expect(fn () => app(CreateUser::class)->handle([
        'name'     => 'Outro',
        'email'    => 'duplicado@clinica.com',
        'password' => 'senha123',
        'roles'    => ['medico'],
    ]))->toThrow(\Illuminate\Validation\ValidationException::class);
});
```

### 2.12 — Teste Pest para fluxo 2FA

```php
<?php

use App\Actions\Profile\ConfirmTwoFactor;
use App\Actions\Profile\EnableTwoFactor;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('enables 2fa and generates secret', function () {
    $user   = User::factory()->create();
    $secret = app(EnableTwoFactor::class)->execute($user);

    expect($secret)->toBeString()->toHaveLength(16)
        ->and($user->fresh()->two_factor_secret)->not->toBeNull();
});

it('confirms 2fa with valid code', function () {
    $user = User::factory()->create();
    app(EnableTwoFactor::class)->execute($user);

    $google2fa = app(Google2FA::class);
    $validCode = $google2fa->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));

    app(ConfirmTwoFactor::class)->execute($user, $validCode);

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('rejects invalid 2fa code', function () {
    $user = User::factory()->create();
    app(EnableTwoFactor::class)->execute($user);

    expect(fn () => app(ConfirmTwoFactor::class)->execute($user, '000000'))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});
```

---

## Seção 3 — Padrões de View Blade

### 3.1 — Estrutura padrão de página CRUD

```blade
{{-- resources/views/livewire/access-control/user-table.blade.php --}}
<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                CONTROLE DE ACESSO
            </p>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">
                Usuários
            </h1>
        </div>
        @can('users.create')
            <button wire:click="openCreate"
                    class="btn-primary flex items-center gap-2">
                <x-heroicon-o-plus class="h-4 w-4" />
                Novo Usuário
            </button>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Buscar por nome ou email..."
               class="input w-72" />

        <select wire:model.live="roleFilter" class="input w-44">
            <option value="">Todos os papéis</option>
            @foreach(\Spatie\Permission\Models\Role::all() as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter" class="input w-36">
            <option value="">Todos</option>
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
        </select>

        <button wire:click="exportJson" class="btn-outline flex items-center gap-2">
            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            JSON
        </button>
    </div>

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        <table class="w-full text-sm">
            <thead class="border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <tr>
                    <th class="table-th">Usuário</th>
                    <th class="table-th">Papel(éis)</th>
                    <th class="table-th">Status</th>
                    <th class="table-th">Criado em</th>
                    <th class="table-th text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($this->users as $user)
                    <tr class="table-row-hover">
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}"
                                         alt="{{ $user->name }}"
                                         class="h-8 w-8 rounded-full object-cover" />
                                @else
                                    <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center shrink-0">
                                        <span class="text-xs font-bold text-white">{{ $user->initials() }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-slate-800 dark:text-slate-100">
                                        {{ $user->name }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-td">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <x-badge color="blue">{{ $role->name }}</x-badge>
                                @endforeach
                            </div>
                        </td>
                        <td class="table-td">
                            <x-badge :color="$user->is_active ? 'green' : 'gray'">
                                {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                            </x-badge>
                        </td>
                        <td class="table-td text-slate-500">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="table-td text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('update', $user)
                                    <button wire:click="openEdit('{{ $user->id }}')"
                                            class="icon-btn text-blue-500">
                                        <x-heroicon-o-pencil class="h-4 w-4" />
                                    </button>
                                    <button wire:click="suspend('{{ $user->id }}')"
                                            class="icon-btn text-amber-500"
                                            title="{{ $user->is_active ? 'Suspender' : 'Ativar' }}">
                                        <x-heroicon-o-power class="h-4 w-4" />
                                    </button>
                                @endcan
                                @can('delete', $user)
                                    <button wire:click="delete('{{ $user->id }}')"
                                            wire:confirm="Tem certeza que deseja remover este usuário?"
                                            class="icon-btn text-red-500">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-td py-12 text-center text-slate-400">
                            Nenhum registro encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Paginação --}}
        <div class="border-t border-slate-100 dark:border-slate-700 px-6 py-4">
            {{ $this->users->links() }}
        </div>
    </div>

    {{-- Formulário (condicional) --}}
    @if($showForm)
        <livewire:access-control.user-form
            :userId="$editingId"
            :key="$editingId ?? 'new'"
        />
    @endif
</div>
```

### 3.2 — Classes CSS utilitárias (adicionar em `resources/css/app.css`)

```css
@layer components {
    /* Botões */
    .btn-primary   { @apply bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-semibold px-4 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500/50; }
    .btn-secondary { @apply bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold px-4 py-2 rounded-lg transition-colors focus:outline-none; }
    .btn-danger    { @apply bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500/50; }
    .btn-outline   { @apply border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-lg transition-colors; }
    .icon-btn      { @apply p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors; }

    /* Layout */
    .card          { @apply bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6; }

    /* Formulários */
    .input         { @apply w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition; }

    /* Tabelas */
    .table-th      { @apply px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider; }
    .table-td      { @apply px-6 py-4; }
    .table-row-hover { @apply hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors; }
}
```

### 3.3 — Regras de uso do Alpine.js vs Livewire

| Situação | Usar |
|----------|------|
| Dropdown, modal, tooltip | Alpine.js (`x-show`, `x-data`) |
| Toggle visual sem estado persistido | Alpine.js |
| Dark mode | Alpine.js + localStorage |
| Filtros que afetam query | Livewire (`wire:model`) |
| Ações que persistem no banco | Livewire (`wire:click`) |
| Polling de dados | Livewire (`wire:poll`) |
| Tabs que mudam UI mas não data | Alpine.js |
| Tabs que pré-carregam dados diferentes | Livewire + `#[Url]` |

### 3.4 — Padrão de `wire:model` por contexto

| Contexto | Diretiva |
|----------|----------|
| Campo de busca com debounce | `wire:model.live.debounce.300ms` |
| Select de filtro | `wire:model.live` |
| Campo de formulário (evitar round-trip a cada tecla) | `wire:model.blur` |
| Select simples de formulário | `wire:model` |
| Checkbox / radio | `wire:model.live` |

---

## Seção 4 — Checklist de revisão de código

Executar antes de commitar qualquer arquivo.

### 4.1 — Model

- [ ] Tem `HasUuids` — UUID como PK em todos os models
- [ ] Tem `SoftDeletes` se listado na seção 1.1 (User, Doctor, Patient, Appointment)
- [ ] Tem `Auditable` + `implements AuditableContract` se listado na seção 1.1
- [ ] Tem `$fillable` definido explicitamente (nunca usar `$guarded = []`)
- [ ] Tem `$casts` corretos — especialmente datas (`datetime`), JSON (`array`), booleans e campos criptografados (`encrypted`)
- [ ] Campos sensíveis listados em `$auditExclude` (password, tokens, secrets)
- [ ] Relacionamentos com tipo de retorno declarado (`BelongsTo`, `HasMany`, etc.)
- [ ] Scopes retornam `Builder` e são prefixados com `scope`

### 4.2 — Action

- [ ] Método único público `handle()`
- [ ] Não faz validação de input (responsabilidade do Form Request ou Livewire `validate()`)
- [ ] Não lança `ValidationException` para erros de domínio (usa domain exceptions)
- [ ] Testada isoladamente via Pest (`it('creates user...')`)
- [ ] Não acessa `request()` ou `session()` diretamente — recebe dados como parâmetros

### 4.3 — Livewire Component

- [ ] Chama `$this->authorize()` no `mount()` ou no início de cada método sensível
- [ ] Usa `#[Url]` em propriedades que devem ser preservadas na URL (filtros, aba ativa)
- [ ] Usa `WithPagination` se tem tabela paginada + chama `$this->resetPage()` nos `updated*` dos filtros
- [ ] Usa `#[Computed]` para queries — evita recalcular desnecessariamente a cada render
- [ ] Usa `wire:confirm="mensagem"` em botões de ações destrutivas (delete, disable)
- [ ] Usa `wire:loading.attr="disabled"` + `wire:loading` nos botões de submit para feedback visual
- [ ] Reseta formulário após salvar (`$this->reset(...)` ou `$this->resetErrorBag()`)
- [ ] Usa `#[On('evento')]` para ouvir eventos de outros componentes
- [ ] Não faz lógica de negócio no componente — delega para Actions

### 4.4 — View Blade

- [ ] Sem PHP logic inline (sem `@php` blocks de lógica — usar propriedades e computed do Livewire)
- [ ] Usa `@can('permissao')` / `@cannot` para ocultar botões sem permissão (não só oculta, mas o backend também protege)
- [ ] Dark mode: toda classe de cor tem par `dark:` (ex: `text-slate-800 dark:text-slate-100`)
- [ ] Usa as classes utilitárias da seção 3.2 (`.btn-primary`, `.card`, `.input`, `.table-th`, etc.)
- [ ] Tabelas têm estado vazio: `@empty` com mensagem "Nenhum registro encontrado."
- [ ] Inputs de número usam `inputmode="numeric"` no mobile
- [ ] Imagens têm `alt` descritivo
- [ ] Sem strings hardcoded em português sem passar por `trans()` quando o projeto escalar

### 4.5 — Rota

- [ ] Tem middleware `auth` no mínimo — nunca rota pública que acessa dados autenticados
- [ ] Tem middleware `verified` exceto nas rotas de challenge 2FA e verificação de e-mail
- [ ] Tem middleware `check2fa` nas rotas dentro do painel
- [ ] Tem nome declarado (`->name('admin.usuarios.index')`)
- [ ] Rotas admin agrupadas em `routes/admin.php`
- [ ] Rotas de perfil agrupadas em `routes/profile.php`
- [ ] Ambos os arquivos incluídos no `routes/web.php` via `require`

### 4.6 — Teste

- [ ] Cobre o happy path (operação com sucesso)
- [ ] Cobre ao menos um unhappy path (acesso negado, validação, estado inválido)
- [ ] Usa `actingAs($user)` — nunca `Auth::login()` nos testes
- [ ] Usa `RefreshDatabase` — banco limpo a cada teste
- [ ] Testa que auditoria foi registrada para operações sensíveis
- [ ] Testa que cache foi invalidado após operações que deveriam invalidá-lo
- [ ] Nomes dos testes em português claro: `it('não permite excluir o próprio usuário')`

---

## Seção 5 — Regras de segurança obrigatórias

Estas regras são inegociáveis em qualquer fase da implementação.

### 5.1 — Autorização

- Toda Action que modifica dados deve verificar permissão **antes** de agir
- O `authorize()` no Livewire não substitui a verificação na Policy — ambos devem existir
- Nunca confiar apenas no `@can` no template — o backend deve rejeitar chamadas não autorizadas

### 5.2 — Upload de arquivos

- Sempre validar MIME type server-side: `mimes:jpg,jpeg,png,webp` (não só extensão)
- Sempre validar tamanho: `max:2048` (2 MB para avatares)
- Nunca armazenar uploads fora do `storage/` — nunca no `public/` diretamente
- Gerar nome único via `storePublicly()` — nunca usar o nome original do arquivo

### 5.3 — Senhas e segredos

- Nunca logar ou auditar: `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`
- `two_factor_secret` obrigatoriamente com cast `encrypted` no model `User`
- Recovery codes: bcrypt individual em cada código antes de armazenar — nunca plaintext
- `Hash::check()` para comparar recovery codes — nunca comparação direta de string

### 5.4 — Consultas ao banco

- Sempre usar Eloquent ou Query Builder com bindings parametrizados — nunca SQL raw com interpolação de variáveis
- Usar eager loading (`with()`) nas listagens com relacionamentos para evitar N+1
- Campos de busca usam `LIKE "%{$term}%"` via binding, nunca concatenação direta

### 5.5 — CSRF e XSS

- CSRF automático via Livewire 3 em todos os `wire:submit` e `wire:click`
- Nunca usar `{!! !!}` com dados de usuário — usar apenas `{{ }}` que escapa HTML
- Exceção permitida: SVG de QR Code gerado internamente pelo servidor (`BaconQrCode`)

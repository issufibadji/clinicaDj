# Plano de Implementação — app-clinica-jm
# Do zero até produção

**Stack:** Laravel 12 + Livewire 3 + Alpine.js + Tailwind CSS 3 + Vite
**Auth:** Laravel Breeze (Livewire stack)
**RBAC:** Spatie Laravel Permission v6
**Auditoria:** owen-it/laravel-auditing
**2FA:** pragmarx/google2fa-laravel + bacon/bacon-qr-code
**Banco:** MySQL 8 (prod) / SQLite (testes)
**Testes:** Pest PHP 3

**Papéis do sistema:**

| Papel | Level | Acesso |
|-------|-------|--------|
| admin | 1 | Total |
| medico | 2 | Clínica (próprio) |
| recepcionista | 3 | Agendamentos + pacientes |
| financeiro | 4 | Pagamentos + relatórios |

**Regra:** Nunca avance para a próxima fase sem o checklist da fase atual 100% marcado.

---

## Dependências entre fases

```
FASE 0 → FASE 1 → FASE 2 → FASE 3 → FASE 4
                                        ↓
FASE 5 (Auth) → FASE 6 (Seeds) → FASE 7 (Menus)
                     ↓
              FASE 8 (Settings) → FASE 9 (Auditoria)
                     ↓
              FASE 10 (Controle Acesso)
                     ↓
              FASE 11 (Perfil + 2FA)
                     ↓
              FASE 12 (Módulos Clínica)
                     ↓
              FASE 13 (Dashboard)
                     ↓
              FASE 14 (Testes) → FASE 15 (Performance)
                                        ↓
                                 FASE 16 (Produção)
```

---

## FASE 0 — Pré-requisitos e ambiente

Verificar o ambiente local antes de criar o projeto.

- [ ] PHP 8.2+ instalado — verificar com `php -v`
  - macOS: `brew install php`
  - Ubuntu: `sudo apt install php8.2 php8.2-cli php8.2-fpm`
  - Windows: `winget install PHP.PHP` ou usar XAMPP/Laragon
- [ ] Extensões PHP obrigatórias presentes: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd` (para QR Code)
  - Verificar: `php -m | grep -E "pdo_mysql|mbstring|openssl|gd|bcmath"`
- [ ] Composer 2.x instalado — verificar com `composer -V`
  - Instalar: `curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer`
- [ ] Node 20+ instalado — verificar com `node -v`
  - Instalar via nvm: `nvm install 20 && nvm use 20`
- [ ] npm atualizado — verificar com `npm -v` (esperado 10+)
- [ ] MySQL 8 rodando com usuário que tem permissão de `CREATE DATABASE`
  - Testar conexão: `mysql -u root -p -e "SELECT VERSION();"`
- [ ] Git configurado com nome e e-mail: `git config --list`
- [ ] Laravel installer global instalado: `composer global require laravel/installer`
  - Verificar: `laravel --version`
- [ ] Porta 8000 livre para `php artisan serve`
  - Verificar: `lsof -i :8000` (Linux/macOS) ou `netstat -ano | findstr :8000` (Windows)

---

## FASE 1 — Criação do projeto Laravel 12

- [ ] Criar projeto com Breeze + stack Livewire + Pest:
  ```bash
  laravel new app-clinica-jm --breeze --stack=livewire --pest
  cd app-clinica-jm
  ```
- [ ] Copiar `.env.example` e gerar chave da aplicação:
  ```bash
  cp .env.example .env
  php artisan key:generate
  ```
- [ ] Editar `.env` com as configurações do banco e da aplicação:
  ```
  APP_NAME="Clínica JM"
  APP_URL=http://localhost:8000
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=app_clinica_jm
  DB_USERNAME=root
  DB_PASSWORD=sua_senha
  SESSION_DRIVER=database
  QUEUE_CONNECTION=database
  ```
- [ ] Criar banco MySQL com charset correto:
  ```sql
  CREATE DATABASE app_clinica_jm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```
- [ ] Testar conexão com o banco:
  ```bash
  php artisan db:show
  ```
- [ ] Rodar migrations padrão do Breeze:
  ```bash
  php artisan migrate
  ```
- [ ] Instalar dependências JS e compilar pela primeira vez:
  ```bash
  npm install && npm run dev
  ```
- [ ] Iniciar servidor de desenvolvimento:
  ```bash
  php artisan serve
  ```
- [ ] Verificar que a tela de login do Breeze carrega em `http://localhost:8000`
- [ ] Inicializar repositório Git e fazer primeiro commit:
  ```bash
  git init && git add . && git commit -m "chore: initial Laravel 12 + Breeze setup"
  ```

---

## FASE 2 — Instalação e configuração dos pacotes

### 2.1 — Spatie Laravel Permission v6

- [ ] Instalar o pacote:
  ```bash
  composer require spatie/laravel-permission
  ```
- [ ] Publicar migration e config:
  ```bash
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  ```
- [ ] Revisar `config/permission.php` — confirmar `guard_name = 'web'`
- [ ] Adicionar coluna `level tinyint unsigned default 99` à migration de `roles` antes de rodar
- [ ] Adicionar coluna `module varchar(50) default 'general'` à migration de `permissions` antes de rodar
- [ ] Rodar a migration do Spatie:
  ```bash
  php artisan migrate
  ```
- [ ] Adicionar trait `HasRoles` ao model `User`
- [ ] Testar via Tinker:
  ```bash
  php artisan tinker
  # \Spatie\Permission\Models\Role::create(['name' => 'admin', 'level' => 1]);
  ```

### 2.2 — owen-it/laravel-auditing

- [ ] Instalar o pacote:
  ```bash
  composer require owen-it/laravel-auditing
  ```
- [ ] Publicar config e migration:
  ```bash
  php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="config"
  php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="migrations"
  ```
- [ ] Revisar `config/audit.php`:
  - Driver: `Database`
  - Threshold: `0` (sem limite automático de registros)
  - Events: `created, updated, deleted, restored`
  - Remover campos sensíveis: `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`
- [ ] Rodar migration da tabela `audits`:
  ```bash
  php artisan migrate
  ```
- [ ] Testar: adicionar `Auditable` trait ao model `User` e fazer update via Tinker, verificar registro em `audits`

### 2.3 — pragmarx/google2fa-laravel + bacon/bacon-qr-code

- [ ] Instalar os pacotes:
  ```bash
  composer require pragmarx/google2fa-laravel
  composer require bacon/bacon-qr-code
  ```
- [ ] Publicar config do Google2FA:
  ```bash
  php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
  ```
- [ ] Criar migration para adicionar colunas 2FA ao `users`:
  ```
  two_factor_secret TEXT nullable
  two_factor_recovery_codes TEXT nullable
  two_factor_confirmed_at TIMESTAMP nullable
  ```
- [ ] Adicionar cast `encrypted` em `two_factor_secret` no model `User`
- [ ] Adicionar cast `datetime` em `two_factor_confirmed_at` no model `User`
- [ ] Rodar migration
- [ ] Registrar alias do middleware `check2fa` em `bootstrap/app.php`

### 2.4 — blade-ui-kit/blade-heroicons

- [ ] Instalar o pacote:
  ```bash
  composer require blade-ui-kit/blade-heroicons
  php artisan vendor:publish --tag=blade-heroicons-config
  ```
- [ ] Testar em uma view temporária: `<x-heroicon-o-home class="w-5 h-5" />` renderiza SVG

### 2.5 — Tabela de sessões (driver database)

- [ ] Criar migration da tabela `sessions`:
  ```bash
  php artisan session:table
  php artisan migrate
  ```
- [ ] Confirmar `SESSION_DRIVER=database` no `.env`
- [ ] Testar: fazer login e verificar registro na tabela `sessions`

---

## FASE 3 — Configuração do Tailwind CSS e Vite

- [ ] Instalar plugins Tailwind:
  ```bash
  npm install -D @tailwindcss/forms @tailwindcss/typography
  ```
- [ ] Configurar `tailwind.config.js`:
  - `content` incluindo `app/Livewire/**/*.php` e `resources/views/**/*.blade.php`
  - Plugins: `@tailwindcss/forms` e `@tailwindcss/typography`
  - Extend colors:
    ```js
    colors: {
      primary:   { DEFAULT: '#10B981', /* tonalidades 50-950 */ },
      secondary: { DEFAULT: '#F59E0B' },
      sidebar:   '#1E293B',
    }
    ```
  - Safelist para classes geradas dinamicamente (badges de status, níveis de papel)
  - `darkMode: 'class'`
- [ ] Configurar `vite.config.js` com hot reload em views Blade e componentes Livewire
- [ ] Configurar `resources/css/app.css`:
  ```css
  @tailwind base;
  @tailwind components;
  @tailwind utilities;
  ```
- [ ] Configurar `resources/js/app.js` com imports de Alpine.js e Livewire
- [ ] Verificar build sem erros:
  ```bash
  npm run build
  ```
- [ ] Verificar que classes custom do design system aparecem no output gerado

---

## FASE 4 — Layout base e design system

- [ ] Criar `resources/views/components/layouts/app.blade.php`:
  - Sidebar fixa (`w-64`) com grupos de menu lidos dinamicamente do cache
  - Topbar (`h-16`) com busca, dark mode toggle, notificações e avatar dropdown
  - Content area responsiva com `{{ $slot }}`
  - Scripts: Alpine.js, Livewire, Chart.js (via CDN ou npm)
  - Classe `dark` gerenciada no `<html>` via Alpine.js + localStorage
- [ ] Criar `resources/views/components/layouts/guest.blade.php`:
  - Layout limpo para login, registro e desafio 2FA
  - Logo centralizado, sem sidebar
- [ ] Criar componentes Blade reutilizáveis em `resources/views/components/`:
  - `card.blade.php` — card com `$slot`, variantes: padrão / sem borda
  - `btn.blade.php` — botão com prop `variant` (primary / secondary / danger / ghost)
  - `badge.blade.php` — badge com prop `color` (green / red / amber / gray)
  - `input.blade.php` — input com label, mensagem de erro e slot de prefixo/sufixo
  - `alert.blade.php` — alerta com tipo (success / warning / error / info) e auto-dismiss Alpine.js
  - `modal.blade.php` — modal Alpine.js reutilizável com slot para header, body e footer
- [ ] Criar partials em `resources/views/partials/`:
  - `sidebar.blade.php` — lê menus do cache, agrupa por `group`, Alpine.js collapse por seção, item ativo por `request()->routeIs()`
  - `topbar.blade.php` — inclui `<x-topbar-user-menu />`, dark mode toggle, badge de notificações
  - `flash.blade.php` — exibe `session('success')` e `session('error')` com Alpine.js auto-dismiss em 4s
- [ ] Implementar dark mode:
  - Classe `dark` adicionada ao `<html>` via Alpine.js `x-init` lendo `localStorage.getItem('theme')`
  - Toggle persiste no localStorage
- [ ] Criar rota de teste: `GET /dashboard` retornando view simples com o layout
- [ ] Verificar que o layout renderiza corretamente com sidebar + topbar + content
- [ ] Verificar que dark mode alterna e persiste após reload

---

## FASE 5 — Autenticação completa

- [ ] Revisar rotas do Breeze: `login`, `logout`, `register`, `password.request`, `verification.notice`
- [ ] Personalizar views de auth (`login.blade.php`, `register.blade.php`) para o design system da clínica (cores, logo, tipografia Inter)
- [ ] Criar middleware `app/Http/Middleware/Check2FA.php`:
  - Lógica: `auth()->check() && user->hasTwoFactorEnabled() && !session('auth.2fa_verified')` → redirect `two-factor.challenge`
  - Ignorar se já está na rota do desafio
- [ ] Registrar alias `check2fa` em `bootstrap/app.php`:
  ```php
  $middleware->alias(['check2fa' => \App\Http\Middleware\Check2FA::class]);
  ```
- [ ] Criar rota `GET /dois-fatores` com middleware `auth, check2fa` (sem `verified`)
- [ ] Criar componente `app/Livewire/Auth/TwoFactorChallenge.php`:
  - Input TOTP de 6 dígitos
  - Toggle para usar código de recuperação (`XXXXX-XXXXX`)
  - `submit()` grava `auth.2fa_verified = true` e redireciona com `redirectIntended()`
- [ ] Criar view `resources/views/livewire/auth/two-factor-challenge.blade.php` com layout guest
- [ ] Testar fluxo completo: register → verify email → login → dashboard
- [ ] Testar fluxo 2FA: ativar no perfil → logout → login → desafio → dashboard
- [ ] Testar: usuário sem 2FA não vê o desafio
- [ ] Testar: código errado exibe erro sem redirecionar

---

## FASE 6 — RBAC: Seeders de base

Executar nesta ordem exata — há dependências entre os seeders.

### 6.1 — RoleSeeder

- [ ] Criar `database/seeders/RoleSeeder.php` com `updateOrCreate` por `name`:
  ```php
  ['name' => 'admin',         'guard_name' => 'web', 'level' => 1]
  ['name' => 'medico',        'guard_name' => 'web', 'level' => 2]
  ['name' => 'recepcionista', 'guard_name' => 'web', 'level' => 3]
  ['name' => 'financeiro',    'guard_name' => 'web', 'level' => 4]
  ```

### 6.2 — PermissionSeeder

- [ ] Criar `database/seeders/PermissionSeeder.php` com `updateOrCreate` por `name`, padrão `modulo.acao`:
  ```
  appointments: view, create, edit, delete
  patients:     view, create, edit, delete
  doctors:      view, create, edit, delete
  payments:     view, create
  reports:      view, export
  rooms:        view, manage
  departments:  view, manage
  insurance:    view, manage
  events:       view, create, edit, delete
  chat:         view, send
  users:        view, create, edit, delete
  roles:        view, manage
  permissions:  view, manage
  system:       audit, menus, settings
  ```

### 6.3 — RolePermissionSeeder

- [ ] Criar `database/seeders/RolePermissionSeeder.php` com `syncPermissions` para cada papel:
  - `admin` → todas as permissões
  - `medico` → `appointments.*`, `patients.*`, `doctors.view`, `events.*`, `chat.*`
  - `recepcionista` → `appointments.*`, `patients.*`, `rooms.view`, `events.view`, `chat.*`
  - `financeiro` → `payments.*`, `reports.*`

### 6.4 — MenuItemSeeder

- [ ] Criar `database/seeders/MenuItemSeeder.php` com `updateOrCreate` por `route`:
  - Grupo **Hospital**: Dashboard, Appointments, Doctors, Patients, Rooms, Payments, Expenses, Departments, Insurance, Events, Chat
  - Grupo **Controle de Acesso**: Usuários, Papéis, Permissões, Vínculo Usuário
  - Grupo **Sistema**: Auditoria, Menus, Configurações
  - Cada item com: `label`, `route`, `icon` (heroicon slug), `group`, `min_level`, `is_visible`, `order`

### 6.5 — SystemSettingSeeder

- [ ] Criar `database/seeders/SystemSettingSeeder.php` com `updateOrCreate` por `key`:
  ```
  clinic_name           (string)  "Clínica JM"
  clinic_phone          (string)  ""
  clinic_address        (string)  ""
  appointments_per_day  (integer) 20
  allow_registration    (boolean) false
  maintenance_mode      (boolean) false
  default_timezone      (string)  "America/Sao_Paulo"
  currency_symbol       (string)  "R$"
  date_format           (string)  "d/m/Y"
  items_per_page        (integer) 15
  ```

### 6.6 — UserSeeder

- [ ] Criar `database/seeders/UserSeeder.php` com um usuário por papel:
  ```
  admin@clinica.com      / password → papel: admin
  medico@clinica.com     / password → papel: medico
  recepcao@clinica.com   / password → papel: recepcionista
  financeiro@clinica.com / password → papel: financeiro
  ```
  - Todos com `email_verified_at = now()` e `is_active = true`

### 6.7 — Verificação

- [ ] Registrar todos os seeders no `DatabaseSeeder.php` na ordem correta
- [ ] Rodar todos os seeders:
  ```bash
  php artisan db:seed
  php artisan permission:cache-reset
  ```
- [ ] Testar login com cada usuário e verificar redirecionamento correto
- [ ] Verificar que admin vê todos os menus e recepcionista vê apenas os permitidos

---

## FASE 7 — Sistema de menus dinâmico

- [ ] Criar migration `create_menu_items_table` com colunas: `id (uuid)`, `label`, `route`, `icon`, `group`, `min_level (tinyint)`, `is_visible (boolean, indexed)`, `order (int)`, `timestamps`
- [ ] Criar model `app/Models/MenuItem.php`:
  - `$fillable` com todos os campos
  - Casts: `is_visible => 'boolean'`, `min_level => 'integer'`, `order => 'integer'`
  - Trait `Auditable`
  - Scope `scopeVisible()` — filtra `is_visible = true`
  - Scope `scopeForLevel(int $level)` — filtra `min_level >= $level`
  - Scope `scopeOrdered()` — ordena por `order ASC`
- [ ] Criar `app/Actions/Admin/System/GetSidebarMenus.php`:
  - `Cache::remember('sidebar.menu.level.'.$userLevel, 3600, fn() => MenuItem::visible()->forLevel($userLevel)->ordered()->get()->groupBy('group'))`
- [ ] Atualizar partial `sidebar.blade.php`:
  - Chama `GetSidebarMenus` e renderiza grupos dinamicamente
  - Item ativo detectado por `request()->routeIs($item->route . '*')`
  - Collapse de seção por grupo via Alpine.js `x-data / x-show`
- [ ] Criar componente `app/Livewire/Admin/System/MenuManager.php` com view:
  - Tabela com todos os itens de menu
  - Toggle `is_visible` → salva + invalida chaves de cache para todos os níveis
  - Dropdown `min_level` → salva + invalida cache
- [ ] Testar: ocultar item via MenuManager → verificar que some da sidebar sem reload manual

---

## FASE 8 — Configurações dinâmicas

- [ ] Criar migration `create_system_settings_table` com colunas: `id (uuid)`, `key (varchar, unique)`, `value (text nullable)`, `type (enum: boolean/integer/decimal/string)`, `label`, `description`, `timestamps`
- [ ] Criar model `app/Models/SystemSetting.php`:
  - `$fillable` com todos os campos
  - Trait `Auditable` com `auditInclude: ['value']`
  - Accessor `getTypedValueAttribute()` — converte `value` conforme `type` com `match`
  - Helper estático `get(string $key, mixed $default = null)` — lê do cache
  - Helper estático `set(string $key, mixed $value)` — salva e invalida cache
- [ ] Criar `app/Actions/Admin/System/SaveSystemSettingAction.php`:
  - Valida tipo antes de salvar
  - Chama `SystemSetting::set()`
- [ ] Criar componente `app/Livewire/Admin/System/SystemSettings.php` com view:
  - Renderiza cada setting conforme o tipo (toggle / input numérico / input texto)
  - `wire:model.blur` para inputs de texto/número
  - Flash "Salvo ✓" por 2s após cada alteração
- [ ] Testar: alterar `clinic_name` → recarregar página → valor persiste

---

## FASE 9 — Auditoria

- [ ] Adicionar trait `Auditable` e interface `AuditableContract` nos models:
  `User`, `Doctor`, `Patient`, `Appointment`, `Payment`, `Role`, `Permission`, `MenuItem`, `SystemSetting`
- [ ] Configurar `config/audit.php`:
  - Driver: `Database`
  - Threshold: `0`
  - Events: `created`, `updated`, `deleted`, `restored`
  - `exclude` campos sensíveis: `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`
- [ ] Criar componente `app/Livewire/Admin/System/AuditLog.php` com view:
  - Filtros: entidade (dropdown com classes auditáveis), evento (created/updated/deleted), date range De/Até
  - Filtros preservados em query params via `#[Url]`
  - Paginação 15 por página
  - Botão "Exportar JSON"
- [ ] Criar modal de diff na view:
  - Exibe `old_values` e `new_values` lado a lado em JSON formatado
  - Destaca campos alterados com classe de cor distinta
- [ ] Testar: editar um usuário → verificar audit com `old_values` e `new_values` corretos
- [ ] Testar: deletar um registro → verificar evento `deleted` registrado

---

## FASE 10 — Controle de Acesso (CRUD)

Implementar nesta ordem para evitar dependências circulares.

### 10.1 — Permissões (`/admin/permissoes`)

- [ ] Criar `app/Policies/PermissionPolicy.php`:
  - `viewAny`: `can('permissions.view')`
  - `create`: `can('permissions.manage')`
  - `delete`: bloqueia se `$permission->roles()->count() > 0`
- [ ] Registrar `PermissionPolicy` no `AppServiceProvider`
- [ ] Criar `app/Actions/Admin/Permissions/CreatePermissionAction.php`
- [ ] Criar `app/Actions/Admin/Permissions/DeletePermissionAction.php`
- [ ] Criar componente `app/Livewire/Admin/Permissions/PermissionTable.php` com view:
  - Listagem agrupada por `module`
  - Busca em tempo real com `wire:model.live`
  - Botão deletar com confirmação Alpine.js
- [ ] Criar componente `app/Livewire/Admin/Permissions/PermissionForm.php` com view:
  - Campos: name (padrão `modulo.acao`), module, guard_name
  - Valida unicidade do nome
- [ ] Adicionar rotas em `routes/admin.php` com middleware `permission:permissions.view`
- [ ] Testar CRUD completo com usuário admin

### 10.2 — Papéis (`/admin/papeis`)

- [ ] Criar `app/Policies/RolePolicy.php`:
  - `delete`: bloqueia se `$role->name === 'admin'` ou `$role->users()->count() > 0`
- [ ] Registrar `RolePolicy`
- [ ] Criar `app/Actions/Admin/Roles/CreateRoleAction.php` — cria papel + `syncPermissions`
- [ ] Criar `app/Actions/Admin/Roles/UpdateRoleAction.php` — pula update do name se admin, invalida cache
- [ ] Criar `app/Actions/Admin/Roles/DeleteRoleAction.php` — desvincula permissões, deleta, invalida cache
- [ ] Criar componente `app/Livewire/Admin/Roles/RoleTable.php` com view
- [ ] Criar componente `app/Livewire/Admin/Roles/RoleForm.php` com view:
  - Multi-select de permissões agrupadas por `module`
  - Campo `level` (1–99)
- [ ] Adicionar rotas com middleware `permission:roles.view`
- [ ] Testar: tentar excluir papel `admin` → bloqueado com mensagem clara

### 10.3 — Usuários (`/admin/usuarios`)

- [ ] Criar `app/Policies/UserPolicy.php`:
  - `update`: verifica `users.edit` + regra admin-apenas-admin
  - `delete`: verifica `users.delete` + não pode deletar a si mesmo + admin-apenas-admin
  - `toggleStatus`: verifica `users.edit` + não pode desativar a si mesmo
- [ ] Registrar `UserPolicy`
- [ ] Criar Actions: `CreateUserAction`, `UpdateUserAction`, `ToggleUserStatusAction`, `ResetUserPasswordAction`, `DeleteUserAction`
- [ ] Criar componente `app/Livewire/Admin/Users/UserTable.php` com view:
  - Filtros: papel, status (ativo/inativo), busca por nome/e-mail
  - Ações inline: toggle status, reset senha (modal com senha gerada), deletar
- [ ] Criar componente `app/Livewire/Admin/Users/UserForm.php` com view:
  - Criar/editar usuário
  - Select de papel
  - Upload de avatar (validado server-side: mime + max 2MB)
- [ ] Adicionar rotas com middleware `permission:users.view`
- [ ] Testar: médico tenta acessar listagem → 403

### 10.4 — Vínculo Usuário (`/admin/vinculo-usuario`)

- [ ] Criar `app/Actions/Admin/UserRoles/AssignUserRolesAction.php` — `syncRoles` + invalida cache
- [ ] Criar componente `app/Livewire/Admin/UserRoles/UserRoleAssignment.php` com view:
  - Painel esquerdo: lista de usuários com busca
  - Painel direito: papéis disponíveis com checkboxes
  - `wire:change` nos checkboxes → salva automaticamente
- [ ] Adicionar rota com middleware `permission:roles.manage`
- [ ] Testar: vincular papel a usuário → verificar auditoria + sidebar atualiza

---

## FASE 11 — Perfil e segurança da conta

### 11.1 — Perfil (`/perfil`)

- [ ] Criar componente `app/Livewire/Profile/UserProfile.php`:
  - Consulta últimas 5 auditorias do próprio usuário em `audits`
  - Read-only — sem formulários
- [ ] Criar view `resources/views/livewire/profile/user-profile.blade.php`:
  - Banner com gradiente + avatar com overflow
  - Grid: informações pessoais, links rápidos, atividade recente
  - Badge de status 2FA (ativo/inativo)
- [ ] Adicionar rota `GET /perfil` → `profile.show` com middleware `auth, check2fa, verified`

### 11.2 — Configurações da conta (`/perfil/configuracoes`)

- [ ] Adicionar helpers ao model `User`:
  - `hasTwoFactorEnabled()` — bool: `two_factor_confirmed_at !== null`
  - `getRecoveryCodes()` — array de hashes descriptografado
  - `storeRecoveryCodes(array $hashedCodes)` — persiste JSON criptografado
  - `validateAndConsumeRecoveryCode(string $plainCode)` — itera com `Hash::check`, remove o encontrado e salva, retorna bool
  - `avatarUrl()` — retorna URL pública ou null
  - `initials()` — retorna 2 letras das iniciais do nome
- [ ] Criar Actions de perfil:
  - `UpdateProfileInformation` — atualiza nome/e-mail/telefone/avatar; se e-mail mudou: `email_verified_at = null` + envia verificação; deleta avatar antigo via `Storage::disk('public')`
  - `UpdatePassword` — `Hash::check` senha atual (lança `ValidationException` se errada), atualiza hash
  - `EnableTwoFactor` — `Google2FA::generateSecretKey()`, salva encrypted, retorna secret + URL do QR
  - `ConfirmTwoFactor` — `Google2FA::verifyKey()`, chama `GenerateRecoveryCodes::generate()`, seta `two_factor_confirmed_at = now()`, retorna plain codes
  - `GenerateRecoveryCodes` — gera 8 códigos `XXXXX-XXXXX`, bcrypt cada um, chama `user->storeRecoveryCodes()`; método `regenerate()` exige confirmação de senha
  - `UseRecoveryCode` — chama `user->validateAndConsumeRecoveryCode()`, lança `ValidationException` se inválido, seta `auth.2fa_verified = true` na sessão
  - `DisableTwoFactor` — valida senha + TOTP, limpa `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`
- [ ] Criar componente `app/Livewire/Profile/AccountSettings.php` com 3 abas Alpine.js:
  - **Aba 1 — Informações Pessoais:** nome, e-mail, telefone, avatar upload (mime validado server-side)
  - **Aba 2 — Segurança:** alterar senha (exige atual), lista de sessões ativas com botão revogar
  - **Aba 3 — 2FA:** 3 estados condicionais (desativado / configurando com QR Code SVG / ativo com opções de regen e desativar)
- [ ] Criar view `resources/views/livewire/profile/account-settings.blade.php`
- [ ] QR Code renderizado como SVG inline via `BaconQrCode\Writer` com `SvgImageBackEnd` (180 px)
- [ ] Criar `routes/profile.php` e incluir no `routes/web.php`
- [ ] Adicionar rota `GET /perfil/configuracoes` → `profile.settings` (tab via query string `?tab=info|security|2fa`)

### 11.3 — Topbar

- [ ] Criar componente Blade `resources/views/components/topbar-user-menu.blade.php`:
  - Links: Meu Perfil, Configurações, Segurança, Dois Fatores, Modo escuro/claro, Sair
  - Ponto verde (`.bg-emerald-500`) ao lado de "Dois Fatores" quando 2FA ativo
  - Alpine.js dropdown com `@keydown.escape.window` e `@click.outside`
- [ ] Incluir `<x-topbar-user-menu />` no layout `app`
- [ ] Testar: links navegam para as abas corretas com query string

---

## FASE 12 — Módulos clínicos

Implementar nesta ordem (menos dependências primeiro).

Para cada módulo, criar:
- Migration + Model (com `Auditable`)
- Policy
- Actions (Create, Update, Delete)
- Livewire Table component (busca + filtros + paginação)
- Livewire Form component (modal ou página)
- View Blade seguindo o design system
- Rota com middleware de permissão correto
- Feature tests (Pest)

### 12.1 — Departments (sem dependências)

- [ ] Migration: `id (uuid)`, `name`, `description nullable`, `is_active (boolean)`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:departments.view`), Testes

### 12.2 — Rooms (depende de Departments)

- [ ] Migration: `id (uuid)`, `name`, `type`, `capacity (int)`, `department_id (fk nullable)`, `is_active`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:rooms.view`), Testes

### 12.3 — Insurance (sem dependências)

- [ ] Migration: `id (uuid)`, `name`, `plan_type`, `contact_phone nullable`, `is_active`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:insurance.view`), Testes

### 12.4 — Doctors (depende de Users, Departments)

- [ ] Migration: `id (uuid)`, `user_id (fk)`, `specialty`, `crm (unique)`, `department_id (fk nullable)`, `is_available (boolean)`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:doctors.view`), Testes

### 12.5 — Patients (sem dependências)

- [ ] Migration: `id (uuid)`, `name`, `cpf (unique)`, `birth_date`, `phone`, `email nullable`, `address (json nullable)`, `insurance_id (fk nullable)`, `timestamps`
- [ ] Model, Policy, Actions, Table (busca por nome/CPF), Form, View, Rota (`permission:patients.view`), Testes

### 12.6 — Appointments (depende de Doctors, Patients, Rooms)

- [ ] Migration: `id (uuid)`, `patient_id (fk)`, `doctor_id (fk)`, `room_id (fk nullable)`, `scheduled_at (datetime, indexed)`, `status (enum: scheduled/confirmed/completed/cancelled, indexed)`, `notes nullable`, `timestamps`
- [ ] Model, Policy, Actions, Table (filtros: data + status + médico), Form, View, Rota (`permission:appointments.view`), Testes

### 12.7 — Payments (depende de Appointments)

- [ ] Migration: `id (uuid)`, `appointment_id (fk)`, `amount (decimal 10,2)`, `method (enum: cash/card/pix/insurance)`, `status (enum: pending/paid/refunded)`, `paid_at nullable`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:payments.view`), Testes

### 12.8 — Expenses (depende de Users)

- [ ] Migration: `id (uuid)`, `description`, `amount (decimal 10,2)`, `category`, `date`, `user_id (fk)`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:payments.view`), Testes

### 12.9 — Events (depende de Users)

- [ ] Migration: `id (uuid)`, `title`, `description nullable`, `start_at (datetime)`, `end_at (datetime)`, `color (varchar 7)`, `user_id (fk)`, `is_public (boolean)`, `timestamps`
- [ ] Model, Policy, Actions, Table, Form, View, Rota (`permission:events.view`), Testes

### 12.10 — Chat (depende de Users — polling 3s)

- [ ] Migration `chat_messages`: `id (uuid)`, `from_user_id (fk)`, `to_user_id (fk)`, `body (text)`, `read_at (timestamp nullable)`, `timestamps`
- [ ] Model, Policy, Actions
- [ ] Livewire component com `wire:poll.3s="loadMessages"` para receber mensagens
- [ ] View, Rota (`permission:chat.view`), Testes

### 12.11 — Reports

- [ ] Criar componente `app/Livewire/Reports/ReportViewer.php`:
  - Relatórios disponíveis: Appointments por período, Receitas vs Despesas, Ocupação de salas
  - Filtros de data range + exportação CSV (job assíncrono se > 1000 registros)
- [ ] View, Rota com `permission:reports.view`, Testes

---

## FASE 13 — Dashboard

- [ ] Criar componente `app/Livewire/Dashboard/StatsCards.php`:
  - KPIs: consultas hoje, novos pacientes, médicos disponíveis, receita do dia
  - Comparativo com dia anterior (flecha ↑↓ + percentual colorido)
  - `wire:poll.30s` para atualização automática
- [ ] Criar componente `app/Livewire/Dashboard/DoctorOnDuty.php`:
  - Médico disponível no horário atual
  - Carrossel com setas (Alpine.js, sem biblioteca externa)
  - Exibe: foto/iniciais, nome, especialidade, próximos horários
- [ ] Criar componente `app/Livewire/Dashboard/AppointmentChart.php`:
  - Dados mensais: consultas agendadas vs realizadas (dois datasets)
  - Chart.js inicializado via Alpine.js `x-init`
  - Suporte a dark mode (cores mudam conforme classe `dark` no `<html>`)
- [ ] Criar componente `app/Livewire/Dashboard/MiniCalendar.php`:
  - Calendário do mês atual
  - Navegação mês anterior/próximo (Alpine.js, sem Livewire round-trip)
  - Destacar dia atual
  - Destacar dias com appointments agendados (pontos coloridos)
- [ ] Criar view `resources/views/livewire/dashboard/index.blade.php` compondo os 4 componentes
- [ ] Rota `GET /dashboard` → `dashboard` com middleware `auth, check2fa, verified`
- [ ] Testar: cada papel vê apenas os KPIs permitidos (médico não vê receitas financeiras)

---

## FASE 14 — Testes

### 14.1 — Testes de autenticação

- [ ] Login com credenciais válidas → redireciona para dashboard
- [ ] Login com credenciais inválidas → mensagem de erro, sem redirecionamento
- [ ] Rate limit após 5 tentativas → resposta 429
- [ ] Registro e verificação de e-mail
- [ ] Challenge 2FA com código TOTP válido → dashboard
- [ ] Challenge 2FA com código TOTP inválido → erro sem logout
- [ ] Challenge 2FA com código de recuperação válido → dashboard, código invalidado
- [ ] Challenge 2FA com código de recuperação já usado → erro
- [ ] Logout limpa `auth.2fa_verified` da sessão

### 14.2 — Testes RBAC

- [ ] Admin acessa `GET /admin/usuarios` → 200
- [ ] Médico acessa `GET /admin/usuarios` → 403
- [ ] Recepcionista acessa `GET /admin/papeis` → 403
- [ ] Papel sem permissão específica recebe 403 na rota protegida
- [ ] Usuário com `is_active = false` não consegue logar

### 14.3 — Testes de componentes Livewire (padrão por componente)

```php
Livewire::actingAs($admin)
    ->test(UserTable::class)
    ->assertSee('Usuários')
    ->set('search', 'admin')
    ->assertSee('admin@clinica.com')
    ->assertDontSee('medico@clinica.com');
```

- [ ] `UserTable` — busca filtra resultados
- [ ] `UserForm` — cria usuário com papel, verifica no banco
- [ ] `RoleTable` — listagem exibe papéis com nível
- [ ] `RoleForm` — cria papel com permissões selecionadas
- [ ] `PermissionTable` — agrupa por módulo
- [ ] `AuditLog` — filtros funcionam, paginação correta
- [ ] `MenuManager` — toggle visibilidade invalida cache
- [ ] `SystemSettings` — salvar `clinic_name` persiste no banco
- [ ] `TwoFactorChallenge` — código válido autentica, inválido retorna erro
- [ ] `AccountSettings` aba info — salva nome/e-mail e atualiza model
- [ ] `AccountSettings` aba 2FA — fluxo completo enable → confirm → disable

### 14.4 — Testes das Actions

- [ ] `CreateUserAction` — valida dados e cria usuário com papel
- [ ] `UpdatePassword` — rejeita senha atual errada com `ValidationException`
- [ ] `EnableTwoFactor` — retorna secret de 16 caracteres válido para TOTP
- [ ] `ConfirmTwoFactor` — código válido salva `two_factor_confirmed_at`
- [ ] `GenerateRecoveryCodes` — gera 8 códigos, todos bcrypt hashados no banco
- [ ] `UseRecoveryCode` — código válido marca sessão, inválido lança exception
- [ ] `SaveSystemSettingAction` — converte tipo `boolean` corretamente

### 14.5 — Testes de cada módulo clínico (template)

- [ ] CRUD completo com usuário admin
- [ ] Soft delete e restore (quando aplicável)
- [ ] Auditoria registra `created`, `updated`, `deleted`
- [ ] Paginação retorna página 2 corretamente
- [ ] Busca em tempo real filtra resultados corretos

### 14.6 — Coverage mínimo

- [ ] Rodar suite completa:
  ```bash
  php artisan test
  ./vendor/bin/pest --coverage --min=70
  ```
- [ ] Corrigir testes falhando antes de avançar para FASE 15

---

## FASE 15 — Performance e cache

- [ ] Instalar Laravel Debugbar em dev e identificar queries N+1:
  ```bash
  composer require barryvdh/laravel-debugbar --dev
  ```
- [ ] Adicionar eager loading nas listagens com relacionamentos:
  - `UserTable`: `with(['roles'])`
  - `AppointmentTable`: `with(['patient', 'doctor', 'room'])`
  - `PaymentTable`: `with(['appointment.patient'])`
- [ ] Configurar cache de sidebar por nível:
  ```php
  Cache::remember('sidebar.menu.level.'.$level, 3600, ...)
  ```
- [ ] Configurar cache de system settings:
  ```php
  Cache::remember('system_settings', 300, ...)
  ```
- [ ] Confirmar que Spatie Permission cache está ativo e que `permission:cache-reset` é chamado após mudanças nos papéis/permissões
- [ ] Adicionar índices de banco para colunas de filtro frequente:
  - `appointments`: `scheduled_at`, `status`, `doctor_id`, `patient_id`
  - `audits`: `user_id`, `auditable_type`, `auditable_id`, `created_at`
  - `menu_items`: `is_visible`, `min_level`
  - `sessions`: `user_id`, `last_activity`
- [ ] Configurar jobs assíncronos para tarefas pesadas (e-mails, exportações CSV):
  ```bash
  php artisan queue:table && php artisan migrate
  ```
- [ ] Confirmar que `QUEUE_CONNECTION=database` está no `.env`
- [ ] Testar queue: disparar job de envio de e-mail e verificar tabela `jobs`

---

## FASE 16 — Preparação para produção

### 16.1 — Variáveis de ambiente (produção)

- [ ] Configurar `.env` de produção:
  ```
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://clinica-jm.com.br
  SESSION_DRIVER=database
  QUEUE_CONNECTION=database
  CACHE_DRIVER=redis
  MAIL_MAILER=smtp
  MAIL_FROM_ADDRESS=noreply@clinica-jm.com.br
  ```
- [ ] Garantir que `APP_KEY` está gerado e nunca commitado no repositório

### 16.2 — Otimizações de build

- [ ] Rodar otimizações Laravel:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  php artisan icons:cache
  php artisan permission:cache-reset
  ```
- [ ] Build dos assets para produção:
  ```bash
  npm run build
  ```
- [ ] Criar link simbólico do storage:
  ```bash
  php artisan storage:link
  ```

### 16.3 — Checklist de segurança pré-deploy

- [ ] `APP_DEBUG=false` em produção
- [ ] `APP_KEY` gerado com `php artisan key:generate`
- [ ] HTTPS configurado com certificado válido (Let's Encrypt ou similar)
- [ ] Rate limiting ativo nas rotas de auth (padrão do Breeze)
- [ ] CSRF ativo (automático no Livewire 3)
- [ ] Headers de segurança configurados no nginx/Apache: `X-Frame-Options: SAMEORIGIN`, `Strict-Transport-Security`, `X-Content-Type-Options: nosniff`
- [ ] Campos sensíveis excluídos da auditoria: `password`, `two_factor_secret`, `two_factor_recovery_codes`, `remember_token`
- [ ] Backups automáticos do banco configurados (cron ou serviço externo)
- [ ] Permissões de diretório corretas: `storage/` e `bootstrap/cache/` com `755`

### 16.4 — Checklist funcional pré-deploy

- [ ] Login + 2FA funcionando em produção com URL real
- [ ] Upload de avatar salva em `storage/app/public/avatars/` e URL pública está acessível
- [ ] E-mails de verificação chegam (testar com conta real)
- [ ] Auditoria registrando corretamente no banco de produção
- [ ] Sidebar carrega menus corretamente para cada papel
- [ ] Configurações do sistema persistem após reiniciar servidor
- [ ] `php artisan test` passa 100% com banco de teste isolado
- [ ] Queue worker rodando como serviço (Supervisor ou systemd)

---

## Resumo de comandos — do zero ao ar

```bash
# FASE 0 — verificar ambiente
php -v && composer -V && node -v && mysql --version

# FASE 1 — criar projeto
laravel new app-clinica-jm --breeze --stack=livewire --pest
cd app-clinica-jm && cp .env.example .env
php artisan key:generate
# editar .env com DB, APP_URL, SESSION_DRIVER=database...
php artisan migrate
npm install && npm run dev
php artisan serve

# FASE 2 — instalar pacotes
composer require spatie/laravel-permission
composer require owen-it/laravel-auditing
composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
composer require blade-ui-kit/blade-heroicons
npm install -D @tailwindcss/forms @tailwindcss/typography

# FASE 2 — publicar configs e migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="config"
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="migrations"
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"
php artisan vendor:publish --tag=blade-heroicons-config
php artisan session:table
php artisan migrate

# FASE 6 — rodar seeds (após migrations estarem todas aplicadas)
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=MenuItemSeeder
php artisan db:seed --class=SystemSettingSeeder
php artisan db:seed --class=UserSeeder
php artisan permission:cache-reset

# DESENVOLVIMENTO (3 terminais)
npm run dev             # terminal 1
php artisan serve       # terminal 2
php artisan queue:work  # terminal 3 (se usar queues)

# TESTES
php artisan test
./vendor/bin/pest --coverage --min=70

# PRODUÇÃO
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache
php artisan permission:cache-reset
php artisan storage:link
npm run build
```

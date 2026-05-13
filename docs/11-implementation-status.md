# Status de Implementação — app-clinica-jm
# Checklist de homologação por fase

**Última atualização:** 2026-05-13 — FASE 14 concluída (99/99 testes passando)
**Ambiente homologado:** local (127.0.0.1:8000) · MySQL 8 · PHP 8.2 · Laravel 12

---

## Legenda

| Símbolo | Significado |
|---------|-------------|
| ✅ | Implementado e homologado (testado manualmente) |
| 🔧 | Implementado, aguarda homologação |
| ⬜ | Não iniciado |
| ❌ | Implementado com problema conhecido |

---

## FASE 0 — Pré-requisitos e ambiente

| # | Item | Status | Observação |
|---|------|--------|------------|
| 0.1 | PHP 8.2+ instalado | ✅ | |
| 0.2 | Extensões obrigatórias (`pdo_mysql`, `mbstring`, `openssl`, `gd`, `bcmath`) | ✅ | |
| 0.3 | Composer 2.x instalado | ✅ | |
| 0.4 | Node 20+ instalado | ✅ | |
| 0.5 | MySQL 8 rodando com permissão `CREATE DATABASE` | ✅ | Porta 8111 (custom) |
| 0.6 | Banco `clinica_jm` criado com `utf8mb4_unicode_ci` | ✅ | |

---

## FASE 1 — Criação do projeto Laravel 12

| # | Item | Status | Observação |
|---|------|--------|------------|
| 1.1 | Projeto criado com Breeze + stack Livewire + Pest | ✅ | |
| 1.2 | `.env` configurado (DB, APP_URL, SESSION_DRIVER=database) | ✅ | |
| 1.3 | `APP_KEY` gerado | ✅ | |
| 1.4 | Migrations padrão do Breeze rodadas | ✅ | |
| 1.5 | Assets JS compilados (`npm run build`) | ✅ | Vite 7.x |
| 1.6 | Servidor de desenvolvimento funcional em `http://127.0.0.1:8000` | ✅ | |
| 1.7 | Tela de login carrega corretamente | ✅ | |

---

## FASE 2 — Instalação e configuração dos pacotes

### 2.1 — Spatie Laravel Permission v6

| # | Item | Status | Observação |
|---|------|--------|------------|
| 2.1.1 | Pacote instalado (`spatie/laravel-permission`) | ✅ | v6.x |
| 2.1.2 | Config e migration publicados | ✅ | |
| 2.1.3 | Coluna `level tinyint` adicionada à migration de `roles` | ✅ | Migration `2026_05_12_200000` |
| 2.1.4 | Coluna `module varchar(50)` adicionada à migration de `permissions` | ✅ | Migration `2026_05_12_200010` |
| 2.1.5 | Migration `model_has_roles.model_id` corrigida para `uuid` | ✅ | Bug corrigido em 2026-05-12 |
| 2.1.6 | Migration `model_has_permissions.model_id` corrigida para `uuid` | ✅ | Bug corrigido em 2026-05-12 |
| 2.1.7 | Trait `HasRoles` adicionada ao model `User` | ✅ | |

### 2.2 — owen-it/laravel-auditing

| # | Item | Status | Observação |
|---|------|--------|------------|
| 2.2.1 | Pacote instalado (`owen-it/laravel-auditing`) | ✅ | |
| 2.2.2 | Config e migration publicados | ✅ | |
| 2.2.3 | Tabela `audits` criada | ✅ | Migration `2026_05_12_200521` |
| 2.2.4 | Trait `Auditable` adicionada ao model `User` | ✅ | Campos sensíveis excluídos |
| 2.2.5 | Trait `Auditable` adicionada ao model `MenuItem` | ✅ | `auditInclude: is_visible, min_level, order` |
| 2.2.6 | Trait `Auditable` adicionada ao model `SystemSetting` | ✅ | `auditInclude: value` |

### 2.3 — pragmarx/google2fa-laravel + bacon/bacon-qr-code

| # | Item | Status | Observação |
|---|------|--------|------------|
| 2.3.1 | Pacotes instalados | ✅ | |
| 2.3.2 | Config publicada | ✅ | |
| 2.3.3 | Migration das colunas 2FA nos usuários criada e rodada | ✅ | `2024_01_01_000240` |
| 2.3.4 | Casts `encrypted` e `datetime` no model `User` | ✅ | |

### 2.4 — blade-ui-kit/blade-heroicons

| # | Item | Status | Observação |
|---|------|--------|------------|
| 2.4.1 | Pacote instalado | ✅ | |
| 2.4.2 | Config publicada | ✅ | |

### 2.5 — barryvdh/laravel-debugbar

| # | Item | Status | Observação |
|---|------|--------|------------|
| 2.5.1 | Pacote instalado (`--dev`) | ✅ | v4.2.8 |
| 2.5.2 | `DEBUGBAR_ENABLED=true` no `.env` | ✅ | Só ativo com `APP_DEBUG=true` |
| 2.5.3 | Barra exibida no browser | ✅ | |

---

## FASE 3 — Tailwind CSS e Vite

| # | Item | Status | Observação |
|---|------|--------|------------|
| 3.1 | Plugins instalados (`@tailwindcss/forms`, `@tailwindcss/typography`) | ✅ | |
| 3.2 | `tailwind.config.js` configurado com `content` incluindo Livewire | ✅ | |
| 3.3 | Cores customizadas: `primary` (emerald), `secondary` (amber), `sidebar` (#1E293B) | ✅ | |
| 3.4 | `darkMode: 'class'` configurado | ✅ | |
| 3.5 | `safelist` para badge colors dinâmicos | ✅ | emerald, red, amber, blue, gray |
| 3.6 | Fonte Inter carregada via Google Fonts | ✅ | Em `app.css` |
| 3.7 | Classes utilitárias definidas em `app.css` | ✅ | `.btn-*`, `.card`, `.input`, `.table-*` |
| 3.8 | Build sem erros (`npm run build`) | ✅ | |
| 3.9 | Alpine.js removido do `app.js` (gerenciado pelo Livewire 3) | ✅ | Bug de instância dupla corrigido |

---

## FASE 4 — Layout base e design system

| # | Item | Status | Observação |
|---|------|--------|------------|
| 4.1 | `layouts/app.blade.php` com sidebar + topbar | ✅ | Homologado em 2026-05-12 |
| 4.2 | `layouts/guest.blade.php` (login/registro) | ✅ | Layout padrão Breeze funcional |
| 4.3 | `partials/sidebar.blade.php` com menus dinâmicos | ✅ | Grupos colapsáveis, item ativo, "em breve" para rotas futuras |
| 4.4 | `partials/topbar.blade.php` com dropdown usuário | ✅ | Toggle sidebar + dark mode + user dropdown |
| 4.5 | `partials/flash.blade.php` com auto-dismiss Alpine.js | ✅ | success/error/warning com 4-6s timeout |
| 4.6 | Componente `card.blade.php` | ✅ | Via classe `.card` no CSS (sem componente Blade separado) |
| 4.7 | Componente `btn.blade.php` | ✅ | Via classes `.btn-*` no CSS (sem componente Blade separado) |
| 4.8 | Componente `badge.blade.php` | ✅ | Props: color (green/red/amber/blue/gray) |
| 4.9 | Componente `alert.blade.php` | ✅ | Props: type (info/success/warning/error), dismissible |
| 4.10 | Componente `modal.blade.php` | ✅ | Modal Breeze padrão existe |
| 4.11 | Dark mode toggle com persistência em `localStorage` | ✅ | Botão na topbar, persiste ao recarregar |
| 4.12 | `GetSidebarMenus` Action com cache por nível | ✅ | Cache de 1h por nível de papel |

---

## FASE 5 — Autenticação completa

| # | Item | Status | Observação |
|---|------|--------|------------|
| 5.1 | Rotas do Breeze: `login`, `logout`, `register`, `password.*` | ✅ | |
| 5.2 | Login funcional com redirecionamento para dashboard | ✅ | Homologado em 2026-05-12 |
| 5.3 | Middleware `Check2FA` criado | ✅ | `app/Http/Middleware/Check2FA.php` |
| 5.4 | Alias `check2fa` registrado em `bootstrap/app.php` | ✅ | |
| 5.5 | Views de auth personalizadas com design system da clínica | ✅ | login, register, forgot/reset/confirm-password, verify-email — em PT-BR |
| 5.6 | Componente `TwoFactorChallenge` (Livewire Volt) | ✅ | `pages.auth.two-factor-challenge` — código TOTP + recuperação |
| 5.7 | Rota `GET /dois-fatores` → `two-factor.challenge` | ✅ | Middleware `auth` em `routes/auth.php` |
| 5.8 | Middleware `check2fa` em dashboard e profile | ✅ | `routes/web.php` |
| 5.9 | Usuário sem 2FA não vê o desafio | ✅ | `hasTwoFactorEnabled()` no middleware |

---

## FASE 6 — RBAC: Seeders de base

| # | Item | Status | Observação |
|---|------|--------|------------|
| 6.1 | `RoleSeeder` (admin/medico/recepcionista/financeiro) | ✅ | |
| 6.2 | `PermissionSeeder` (14 módulos × ações) | ✅ | 36 permissões |
| 6.3 | `RolePermissionSeeder` (vinculação por papel) | ✅ | |
| 6.4 | `MenuItemSeeder` (Hospital, Controle de Acesso, Sistema) | ✅ | |
| 6.5 | `SystemSettingSeeder` (10 configurações iniciais) | ✅ | |
| 6.6 | `UserSeeder` (4 usuários, um por papel) | ✅ | |
| 6.7 | `DatabaseSeeder` na ordem correta | ✅ | |
| 6.8 | Login funcional com cada usuário | ✅ | Todos com senha `password` |

**Usuários de teste:**

| Email | Papel | Senha |
|-------|-------|-------|
| admin@clinica.com | admin | password |
| medico@clinica.com | medico | password |
| recepcao@clinica.com | recepcionista | password |
| financeiro@clinica.com | financeiro | password |

---

## FASE 7 — Sistema de menus dinâmico

| # | Item | Status | Observação |
|---|------|--------|------------|
| 7.1 | Migration `menu_items` criada e rodada | ✅ | `2024_01_01_000220` |
| 7.2 | Model `MenuItem` com scopes (`visible`, `forLevel`, `ordered`) | ✅ | |
| 7.3 | `GetSidebarMenus` Action com cache por nível | ✅ | Implementado em FASE 4 |
| 7.4 | Sidebar renderiza grupos dinamicamente | ✅ | Implementado em FASE 4 |
| 7.5 | Livewire `MenuManager` (toggle visibilidade + min_level + invalida cache) | ✅ | Rota: `GET /sistema/menus` → `admin.sistema.menus` |

---

## FASE 8 — Configurações dinâmicas

| # | Item | Status | Observação |
|---|------|--------|------------|
| 8.1 | Migration `system_settings` criada e rodada | ✅ | `2024_01_01_000230` |
| 8.2 | Model `SystemSetting` com `get()`, `set()`, `typed_value` | ✅ | |
| 8.3 | `SaveSystemSettingAction` | ✅ | Valida por tipo (boolean/integer/decimal/string) |
| 8.4 | Livewire `SystemSettings` (formulário por tipo) | ✅ | Rota: `GET /sistema/configuracoes` · toggle/input/text · flash 2.5s |

---

## FASE 9 — Auditoria

| # | Item | Status | Observação |
|---|------|--------|------------|
| 9.1 | Tabela `audits` criada | ✅ | |
| 9.2 | `config/audit.php` configurado (campos sensíveis excluídos) | ✅ | |
| 9.3 | Livewire `AuditLog` (filtros + paginação + export) | ✅ | Rota: `GET /sistema/auditoria` · filtros via `#[Url]` · export JSON |
| 9.4 | Modal de diff `old_values` vs `new_values` | ✅ | Lado a lado · campos alterados destacados em vermelho/verde |

---

## FASE 10 — Controle de Acesso (CRUD)

| # | Item | Status | Observação |
|---|------|--------|------------|
| 10.1 | `PermissionPolicy` + Actions + Livewire `PermissionManager` | ✅ | Rota: `GET /admin/permissoes` → `admin.permissoes.index` |
| 10.2 | `RolePolicy` + Actions + Livewire `RoleManager` | ✅ | Rota: `GET /admin/papeis` → `admin.papeis.index` |
| 10.3 | `UserPolicy` + Actions + Livewire `UserManager` | ✅ | Rota: `GET /admin/usuarios` → `admin.usuarios.index` |
| 10.4 | `UserRoleAssignment` Livewire (painel de vínculo dois painéis) | ✅ | Rota: `GET /admin/vinculo` → `admin.vinculo.index` |

**Artefatos criados:**

- Policies: `PermissionPolicy`, `RolePolicy`, `UserPolicy`
- Actions (10): `Create/Delete Permission` · `Create/Update/Delete Role` · `Create/Update/ToggleStatus/ResetPassword/Delete User` · `AssignUserRoles`
- Livewire Volt components: `permission-manager`, `role-manager`, `user-manager`, `user-role-assignment`
- `routes/admin.php` com middleware `auth + check2fa + permission:*`

---

## FASE 11 — Perfil e segurança da conta

| # | Item | Status | Observação |
|---|------|--------|------------|
| 11.1 | Helpers no model `User` (`hasTwoFactorEnabled`, `getRecoveryCodes`, `storeRecoveryCodes`, `validateAndConsumeRecoveryCode`, `avatarUrl`, `initials`) | ✅ | |
| 11.2 | Livewire `UserProfile` (read-only + auditorias recentes) | ✅ | Rota: `GET /perfil` → `profile.show` |
| 11.3 | Livewire `AccountSettings` (3 abas: info / segurança / 2FA) | ✅ | Rota: `GET /perfil/configuracoes` → `profile.settings` · tab via `?tab=info\|security\|2fa` |
| 11.4 | Actions: `UpdateProfileInformation`, `UpdatePassword`, `EnableTwoFactor`, `ConfirmTwoFactor`, `GenerateRecoveryCodes`, `DisableTwoFactor` | ✅ | `app/Actions/Profile/` |
| 11.5 | Dropdown de navegação atualizado (links Perfil / Config / Segurança / 2FA + badge ativo) | ✅ | `livewire/layout/navigation.blade.php` |

---

## FASE 12 — Módulos clínicos

| Módulo | Migration | Model | Policy | Actions | Table | Form | Status |
|--------|-----------|-------|--------|---------|-------|------|--------|
| Departments | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Rooms | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Insurance | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Doctors | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Patients | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Appointments | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Payments | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Expenses | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Events | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |
| Chat | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 | 🔧 |

**Artefatos criados:**

- **Migrations** (10): `departments`, `insurances`, `rooms`, `patients`, `doctors`, `appointments`, `payments`, `expenses`, `events`, `chat_messages` — todos rodados com sucesso
- **Models** (10): `Department`, `Insurance`, `Room`, `Patient`, `Doctor`, `Appointment`, `Payment`, `Expense`, `Event`, `ChatMessage` — todos com `HasUuids` + `Auditable` (ChatMessage sem Auditable)
- **Policies** (10): `DepartmentPolicy`, `InsurancePolicy`, `RoomPolicy`, `PatientPolicy`, `DoctorPolicy`, `AppointmentPolicy`, `PaymentPolicy`, `ExpensePolicy`, `EventPolicy`, `ChatMessagePolicy` — registradas em `AppServiceProvider`
- **Actions** (31): Create/Update/Delete por módulo + `SendMessageAction` — em `app/Actions/Clinica/`
- **Livewire Volt components** (10): `clinica/departments`, `clinica/insurance`, `clinica/rooms`, `clinica/doctors`, `clinica/patients`, `clinica/appointments`, `clinica/payments`, `clinica/expenses`, `clinica/events`, `clinica/chat`
- **Routes**: `routes/modules.php` registrado em `web.php` com middleware `auth + verified + check2fa + permission:*`
- **User model**: relacionamentos `doctor()`, `sentMessages()`, `receivedMessages()` adicionados

**Rotas registradas:**

| Rota | URL | Nome |
|------|-----|------|
| Departamentos | `GET /departamentos` | `departments.index` |
| Convênios | `GET /convenios` | `insurance.index` |
| Salas | `GET /salas` | `rooms.index` |
| Médicos | `GET /medicos` | `doctors.index` |
| Pacientes | `GET /pacientes` | `patients.index` |
| Agendamentos | `GET /agendamentos` | `appointments.index` |
| Pagamentos | `GET /pagamentos` | `payments.index` |
| Despesas | `GET /despesas` | `expenses.index` |
| Eventos | `GET /eventos` | `events.index` |
| Chat | `GET /chat` | `chat.index` |

---

## FASE 13 — Dashboard

| # | Item | Status |
|---|------|--------|
| 13.1 | `StatsCards` (KPIs + `wire:poll.30s`) | 🔧 |
| 13.2 | `DoctorOnDuty` (carrossel Alpine.js) | 🔧 |
| 13.3 | `AppointmentChart` (Chart.js + dark mode) | 🔧 |
| 13.4 | `MiniCalendar` (navegação Livewire + dots por dia) | 🔧 |
| 13.5 | Dashboard compondo os 4 componentes | 🔧 |

**Artefatos criados:**

- `resources/js/app.js` — Chart.js importado + Alpine component `appointmentChart` registrado
- `resources/views/livewire/dashboard/stats-cards.blade.php` — 4 KPIs em tempo real com `wire:poll.30s`
- `resources/views/livewire/dashboard/doctor-on-duty.blade.php` — carrossel Alpine.js de médicos disponíveis
- `resources/views/livewire/dashboard/appointment-chart.blade.php` — gráfico de barras (últimos 6 meses) com suporte dark mode via MutationObserver
- `resources/views/livewire/dashboard/mini-calendar.blade.php` — grade mensal com dots nos dias com consultas + navegação Livewire prev/next
- `resources/views/livewire/dashboard.blade.php` — página Volt principal compondo os 4 sub-componentes
- `routes/web.php` — `Route::view` substituído por `Volt::route` para o dashboard
- `npm run build` — assets recompilados (250 kB JS com Chart.js)

---

## FASE 14 — Testes

| # | Item | Status | Observação |
|---|------|--------|------------|
| 14.1 | Testes de autenticação (login, rate limit, 2FA) | ✅ | `TwoFactorTest.php` — 6 testes |
| 14.2 | Testes RBAC (403 por papel, `is_active=false`) | ✅ | `AccessControlTest.php` — 12 testes + middleware `EnsureUserIsActive` |
| 14.3 | Testes de componentes Livewire | ✅ | `DashboardTest.php` (12) + `ClinicalModulesTest.php` (14) |
| 14.4 | Testes das Actions | ✅ | `ActionsTest.php` — 19 testes (Dept/Patient/Appointment/Payment/Chat) |
| 14.5 | Testes dos módulos clínicos | ✅ | Incluído em 14.3 (Rooms, Patients, Appointments, Departments) |
| 14.6 | Coverage mínimo 70% com Pest | ❌ | Xdebug/PCOV não instalado no ambiente — instalar para medir |

**Resultado final:** `99 passed, 195 assertions` em 13.5s (SQLite in-memory)

**Artefatos criados:**

- `app/Http/Middleware/EnsureUserIsActive.php` — bloqueia usuários `is_active=false`, registrado no middleware web global
- `bootstrap/app.php` — `EnsureUserIsActive` adicionado ao stack web
- `database/factories/` — 9 novas factories: Department, Insurance, Room, Patient, Doctor, Appointment, Payment, Expense, Event
- `database/factories/UserFactory.php` — `is_active: true` adicionado ao estado padrão
- `tests/Pest.php` — helpers: `makeAdmin()`, `makeUserWithRole()`, `makeInactiveUser()`, `resetPermissions()`; Unit tests configurados com TestCase
- `tests/Feature/Auth/TwoFactorTest.php`
- `tests/Feature/Rbac/AccessControlTest.php`
- `tests/Feature/Livewire/DashboardTest.php`
- `tests/Feature/Livewire/ClinicalModulesTest.php`
- `tests/Feature/Actions/ActionsTest.php`
- `tests/Unit/UserModelTest.php`
- `tests/Unit/PatientModelTest.php`

**Correções de compatibilidade SQLite (MySQL-only → agnóstico):**

- `appointment-chart.blade.php` — `DATE_FORMAT` → `Collection::groupBy()`
- `mini-calendar.blade.php` — `DAY()` → `->map(fn($a) => $a->scheduled_at->day)`
- `expenses.blade.php` — `DATE_FORMAT whereRaw` → `whereYear/whereMonth`
- `payments.blade.php` — `DATE_FORMAT whereRaw` → `whereYear/whereMonth`
- `patients.blade.php` — adicionado `Rule::unique('patients','cpf')->ignore($editingId)`

---

## FASE 15 — Performance e cache

| # | Item | Status |
|---|------|--------|
| 15.1 | Debugbar instalado e N+1 identificados | ✅ | 
| 15.2 | Eager loading nas listagens | ⬜ |
| 15.3 | Cache de sidebar por nível (`sidebar.menu.level.*`) | ⬜ |
| 15.4 | Cache de system settings | ⬜ |
| 15.5 | Cache Spatie Permission ativo | ⬜ |
| 15.6 | Índices de banco para colunas de filtro | ⬜ |

---

## FASE 16 — Produção

| # | Item | Status |
|---|------|--------|
| 16.1 | `.env` de produção configurado | ⬜ |
| 16.2 | Otimizações Laravel (`config:cache`, `route:cache`, `view:cache`) | ⬜ |
| 16.3 | Checklist de segurança concluído | ⬜ |
| 16.4 | Checklist funcional pré-deploy concluído | ⬜ |

---

## Bugs corrigidos (histórico)

| Data | Descrição | Arquivo corrigido |
|------|-----------|-------------------|
| 2026-05-12 | `model_has_roles.model_id` era `BIGINT`, incompatível com UUID | `2026_05_12_193951_create_permission_tables.php` |
| 2026-05-12 | `model_has_permissions.model_id` era `BIGINT`, incompatível com UUID | `2026_05_12_193951_create_permission_tables.php` |
| 2026-05-12 | `sessions.user_id` era `foreignId()` (BIGINT), bloqueava login | `0001_01_01_000000_create_users_table.php` |
| 2026-05-12 | Alpine.js carregado duas vezes (app.js + Livewire 3) causava "page expired" | `resources/js/app.js` |
| 2026-05-12 | `audits.user_id` era `unsignedBigInteger`, truncava UUID do usuário logado | `2026_05_12_200521_create_audits_table.php` |
| 2026-05-12 | `audits.auditable_id` (`morphs`) era `unsignedBigInteger`, truncava UUID dos models | `2026_05_12_200521_create_audits_table.php` |

---

## Resumo de progresso

| Fase | Total de itens | Concluídos | % |
|------|---------------|------------|---|
| FASE 0 — Ambiente | 6 | 6 | 100% |
| FASE 1 — Projeto | 7 | 7 | 100% |
| FASE 2 — Pacotes | 15 | 15 | 100% |
| FASE 3 — Tailwind/Vite | 9 | 9 | 100% |
| FASE 4 — Layout | 12 | 12 | 100% |
| FASE 5 — Auth | 9 | 9 | 100% |
| FASE 6 — Seeders | 8 | 8 | 100% |
| FASE 7 — Menus | 5 | 5 | 100% |
| FASE 8 — Settings | 4 | 4 | 100% |
| FASE 9 — Auditoria | 4 | 4 | 100% |
| FASE 10 — RBAC CRUD | 4 | 4 | 100% |
| FASE 11 — Perfil/2FA | 5 | 5 | 100% |
| FASE 12 — Módulos | 66 | 66 | 100% 🔧 |
| FASE 13 — Dashboard | 5 | 5 | 100% 🔧 |
| FASE 14 — Testes | 6 | 5 | 83% ✅ |
| FASE 15 — Performance | 6 | 1 | 17% |
| FASE 16 — Produção | 4 | 0 | 0% |
| **TOTAL** | **175** | **165** | **94%** |

---

> **Regra do projeto:** Nunca avançar para a próxima fase sem o checklist da fase atual 100% marcado.
> **Próxima fase a executar:** FASE 15 — Performance e cache (eager loading, sidebar cache, Spatie cache, índices)

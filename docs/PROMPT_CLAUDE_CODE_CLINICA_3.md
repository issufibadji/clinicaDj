# PROMPT PARA CLAUDE CODE — app-clinica-jm
# Cole este arquivo completo em uma sessão nova no Claude Code
# Versão 2.0 — inclui Controle de Acesso + Módulos de Sistema

---

Você é um arquiteto sênior Laravel com expertise em Livewire 3, Alpine.js e Tailwind CSS.

**Sua missão agora é APENAS documentar.** Não crie nenhum arquivo PHP, migration, controller,
view ou componente Livewire até que todos os documentos estejam completos e eu confirme
com "pode começar o código".

Crie a pasta `docs/` na raiz do projeto com estes arquivos, nesta ordem:

```
docs/
├── 01-project.md
├── 02-architecture.md
├── 03-database.md
├── 04-api-routes.md
├── 05-components.md
├── 06-design-system.md
└── 07-setup.md
```

---

## CONTEXTO DO PROJETO

**Nome:** app-clinica-jm
**Tipo:** Painel administrativo para clínica médica (SPA-like com Livewire)
**Inspiração visual:** Mediline Dashboard + eProduct Admin

Layout:
- Sidebar azul-escura (#1E3A5F) com grupos de menu rotulados, ícones e labels
- Topbar branca: busca global, toggle dark/light, sino notificações, seletor de idioma, avatar
- KPI cards: verde (#10B981) primário, âmbar (#F59E0B) secundário
- Gráfico de barras mensais com dois datasets
- Mini-calendário lateral
- Dark mode via toggle (classe `dark` na tag `<html>`)
- Botão flutuante "Ver Loja Pública" no rodapé da sidebar

---

## STACK DEFINITIVA (não alterar, não sugerir alternativas)

| Camada | Tecnologia | Versão |
|--------|-----------|--------|
| Runtime | PHP | 8.2+ |
| Framework | Laravel | 12.x |
| Reatividade | Livewire | 3.x |
| JS leve | Alpine.js | 3.x |
| CSS | Tailwind CSS | 3.x |
| HTTP async | Axios | latest |
| Build | Vite | latest |
| Auth | Laravel Breeze (Livewire stack) | latest |
| RBAC | **Spatie Laravel Permission v6** | v6 |
| Auditoria | **owen-it/laravel-auditing** | latest |
| Gráficos | Chart.js | 4.x |
| Banco (prod) | MySQL | 8.0 |
| Banco (test) | SQLite | — |
| Testes | Pest PHP | 3.x |
| Análise | PHPStan | nível 5 |
| Style | Laravel Pint | latest |

---

## GRUPOS DE FUNCIONALIDADES

O sistema é dividido em 4 grupos, exatamente como aparece na sidebar:

---

### GRUPO 1 — CLÍNICA (módulo principal)

KPIs e operação do dia a dia:

1. **Dashboard** — KPI cards (Appointments, New Admit, Operations, Doctors, Nurses, Earnings), gráfico "Hospital Survey" e mini-calendário
2. **Appointments** — agendamentos com status (agendado / confirmado / cancelado / realizado)
3. **Doctors** — cadastro, especialidades, disponibilidade diária
4. **Patients** — cadastro, prontuário, histórico de consultas
5. **Room Allotments** — alocação de leitos e salas cirúrgicas
6. **Payments** — registro e controle financeiro por consulta
7. **Expenses Report** — relatório de despesas operacionais
8. **Departments** — gestão de departamentos da clínica
9. **Insurance Company** — convênios médicos credenciados
10. **Events** — agenda de eventos, cirurgias e reuniões
11. **Chat** — mensagens internas entre equipe

---

### GRUPO 2 — CONTROLE DE ACESSO

Inspirado no módulo "Controle de Acesso" do eProduct. Totalmente CRUD dinâmico.
Usa **Spatie Laravel Permission v6** como engine.

#### 2.1 — Usuários (`/admin/usuarios`)

CRUD completo de usuários do sistema.

Listagem (tabela paginada, busca em tempo real):
- Colunas: Avatar, Nome, Email, Papel(éis), Status (ativo/inativo), Criado em, Ações
- Ações por linha: Editar, Suspender/Ativar, Redefinir senha, Excluir (soft delete)
- Filtros: por papel, por status, por data de cadastro
- Botão exportar JSON

Formulário criar/editar:
- Nome, Email, Senha (criar) / Redefinir senha (editar)
- Avatar (upload)
- Telefone
- Papéis (multi-select dos papéis cadastrados)
- Status ativo/inativo
- 2FA: ativar/desativar por usuário

> **Regra:** Nenhum usuário pode excluir a si mesmo.
> **Regra:** Apenas `admin` pode criar outros admins.

---

#### 2.2 — Papéis (`/admin/papeis`)

CRUD de roles (Spatie `Role`).

Listagem:
- Colunas: Nome do papel, Slug (guard_name), Nº de permissões, Nº de usuários, Ações
- Ação: Editar nome, Ver permissões vinculadas, Excluir (se sem usuários vinculados)

Formulário criar/editar:
- Nome do papel (ex: "Médico", "Recepcionista")
- Guard: `web` (padrão)
- Multi-select de permissões (agrupadas por módulo)
- Preview: lista de usuários com este papel

Papéis iniciais (seed):
| Papel | Nível | Descrição |
|-------|-------|-----------|
| admin | 1 | Acesso total ao sistema |
| medico | 2 | Agenda, prontuários, consultas próprias |
| recepcionista | 3 | Agendamentos, pacientes, sala |
| financeiro | 4 | Pagamentos, relatórios financeiros |

> **Regra:** Papel `admin` não pode ser excluído nem editado pelo nome.
> **Regra:** Usuário sem papel não acessa o painel.

---

#### 2.3 — Permissões (`/admin/permissoes`)

CRUD de permissions (Spatie `Permission`).

Listagem:
- Colunas: Nome da permissão, Módulo (group), Papéis que a possuem, Ações
- Agrupamento por módulo na tabela
- Filtro por módulo

Formulário criar:
- Nome da permissão no padrão `modulo.acao`
  Ex: `appointments.view`, `appointments.create`, `patients.delete`, `system.audit`
- Módulo/grupo (dropdown dos módulos existentes ou novo)
- Guard: `web`

Permissões iniciais (seed obrigatório por módulo):
```
appointments.view / create / edit / delete
patients.view / create / edit / delete
doctors.view / create / edit / delete
payments.view / create
reports.view / export
rooms.view / manage
departments.view / manage
insurance.view / manage
events.view / create / edit / delete
chat.view / send
users.view / create / edit / delete
roles.view / manage
permissions.view / manage
system.audit / menus / settings
```

> **Regra:** Permissões não podem ser excluídas se vinculadas a papéis ativos.

---

#### 2.4 — Vínculo Usuário (`/admin/vinculo-usuario`)

Tela dedicada para associar/desassociar usuários ↔ papéis rapidamente.

Layout: dois painéis lado a lado (Alpine.js)
- Painel esquerdo: busca e lista de usuários
- Painel direito: papéis disponíveis com checkboxes
- Salva via Livewire sem reload de página
- Exibe papéis atuais do usuário selecionado
- Alterações registradas automaticamente na auditoria

---

### GRUPO 3 — SISTEMA

Funções administrativas do sistema, inspiradas no eProduct.

#### 3.1 — Auditoria (`/admin/sistema/auditoria`)

Tela somente-leitura. Usa **owen-it/laravel-auditing**.

Funcionalidades (idêntico ao eProduct):
- Tabela: Data/Hora, Usuário, Ação (created/updated/deleted), Entidade, Antes, Depois, IP
- Filtros:
  - Dropdown "Todas entidades" → lista dinâmica das entidades auditadas
  - Dropdown "Todas ações" → created / updated / deleted / restored
  - Date range: De → Até (date picker)
  - Botão "Filtrar" e "Limpar"
- Paginação: 15 registros por página
- Botão **Exportar JSON** (canto superior direito)
- Colunas "Antes" e "Depois": diff em JSON (modal ao clicar)
- URL com query params: `?from=2026-04-09&to=2026-04-09&entity=Patient&action=updated&page=1`

Entidades auditadas por padrão:
`User, Doctor, Patient, Appointment, Payment, Role, Permission, MenuItem, SystemSetting`

> **Regra:** Apenas `admin` acessa a auditoria.
> **Regra:** Registros de auditoria nunca são deletados.

---

#### 3.2 — Menus (`/admin/sistema/menus`)

Controle dinâmico de visibilidade dos itens da sidebar. Idêntico ao eProduct.

Funcionalidades:
- Tabela: Menu (label), Rota, Nível mínimo, Visível (toggle)
- Dropdown "Nível mínimo": lista os papéis com nível numérico
- Toggle "Visível/Oculto": salva imediatamente via Livewire
- Menus ocultos não aparecem na sidebar para nenhum usuário
- Menu com nível mínimo só aparece para papéis com nível ≤ ao definido

Tabela `menu_items`:
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint PK | |
| label | string | Nome na sidebar |
| route | string | Rota nomeada |
| icon | string | Classe do ícone |
| group | string | Grupo (CLÍNICA, CONTROLE DE ACESSO, SISTEMA) |
| min_level | tinyint | Nível mínimo de papel |
| is_visible | boolean | Visível globalmente |
| order | tinyint | Ordem de exibição |

> **Regra:** Sidebar lê `menu_items` com cache de 60s.
> **Regra:** `is_visible = false` oculta para TODOS, inclusive admin.
> **Regra:** Apenas `admin` pode editar menus.

---

#### 3.3 — Configurações (`/admin/sistema/configuracoes`)

Configurações dinâmicas. Cada configuração é um registro em `system_settings`.
Edição inline, sem formulário separado — idêntico ao eProduct.

Configurações iniciais (seed):
| Chave | Tipo | Default | Label |
|-------|------|---------|-------|
| allow_public_booking | boolean | true | Agendamento Público Ativo |
| allow_patient_registration | boolean | true | Permitir Novos Cadastros |
| appointment_fee | decimal | 150.00 | Taxa de Consulta (R$) |
| max_daily_appointments | integer | 20 | Máx. Consultas por Dia |
| clinic_name | string | Clínica DR.João Mendes | Nome da Clínica |
| clinic_cnpj | string | — | CNPJ da Clínica |
| clinic_phone | string | — | Telefone |
| clinic_address | string | — | Endereço |
| email_notifications | boolean | true | Notificações por Email |
| default_appointment_duration | integer | 30 | Duração Padrão (min) |

Comportamento por tipo:
- `boolean` → toggle switch (Ativado/Desativado) — salva ao clicar
- `decimal` / `integer` → input numérico com spinner — salva ao `blur`
- `string` → input texto inline — salva ao `blur`
- Implementado com `wire:model.blur` + Action dedicada
- Cada save gera flash "Salvo ✓" por 2s

> **Regra:** Apenas `admin` acessa configurações.
> **Regra:** Alterações em configurações são auditadas.

---

## TAREFA 1 — docs/01-project.md

```markdown
# Projeto — app-clinica-jm

## 1.1 Objetivo
## 1.2 Grupos funcionais e público-alvo por papel
## 1.3 Funcionalidades MVP (v1) — por grupo
## 1.4 Fora de escopo no MVP (backlog v2+)
## 1.5 Requisitos não-funcionais
  - Performance: < 200ms listagens, < 500ms filtros auditoria
  - Acessibilidade: WCAG 2.1 AA
  - Responsividade: desktop-first (min 1024px)
  - Segurança: OWASP Top 10
## 1.6 Métricas de sucesso do MVP
```

---

## TAREFA 2 — docs/02-architecture.md

```markdown
# Arquitetura do Sistema

## 2.1 Decisões de arquitetura e justificativas
  - Por que Livewire 3 (sem SPA completo)
  - Por que Alpine.js junto ao Livewire
  - Por que Spatie Permission (vs ACL manual)
  - Por que owen-it auditing (vs log manual)
  - Estratégia de cache para menu_items

## 2.2 Estrutura de pastas completa
  app/
  ├── Actions/
  │   ├── Users/
  │   ├── Appointments/
  │   └── System/
  ├── Http/
  │   ├── Middleware/
  │   └── Requests/
  ├── Livewire/
  │   ├── Dashboard/
  │   ├── Appointments/
  │   ├── Doctors/
  │   ├── Patients/
  │   ├── AccessControl/    ← Usuarios, Papeis, Permissoes, VinculoUsuario
  │   ├── System/           ← Auditoria, Menus, Configuracoes
  │   └── Shared/           ← Sidebar, Topbar, NotificationBell
  ├── Models/
  └── Policies/

## 2.3 Padrões de código
  - Controller → Action → Model (sem Repository)
  - Form Requests para toda validação
  - Resources para respostas JSON
  - Events + Listeners para side-effects
  - Policies para autorização nos componentes Livewire

## 2.4 Fluxo de autenticação (diagrama ASCII)
  Login → Check2FA? → CheckEmail → CheckPapel → Dashboard

## 2.5 Fluxo de autorização RBAC
  - Como Spatie Permission v6 funciona
  - $this->authorize() em Livewire
  - @can nas views Blade
  - Role-based vs permission-based checks

## 2.6 Sistema de menus dinâmicos
  - Sidebar lê menu_items com cache de 60s
  - Filtra is_visible = true
  - Filtra min_level <= nível do papel do usuário logado
  - Cache invalidado ao salvar em MenuManager

## 2.7 Middleware stack (ordem exata)
  web → auth → verified → CheckPermission

## 2.8 Estratégia de testes
  - Unit: Actions, Policies
  - Feature: Livewire, HTTP endpoints
  - Coverage mínimo: 70%

## 2.9 Segurança
  - CSRF automático (Livewire 3)
  - XSS: Blade {{ }} escapa
  - Rate limiting: 5 tentativas login/min
  - Auditoria de todas as operações sensíveis
```

---

## TAREFA 3 — docs/03-database.md

Para cada tabela usar este formato:

```markdown
### Tabela: nome_da_tabela

| Coluna | Tipo | Nullable | Default | Índice | Descrição |
|--------|------|----------|---------|--------|-----------|

Relacionamentos: ...
Soft delete: sim/não
Auditoria owen-it: sim/não
```

Tabelas obrigatórias — CLÍNICA:
1. `users` — UUID, name, email, password, avatar, phone, is_active, email_verified_at
2. `doctors` — user_id FK, specialty, crm, available_from, available_to, is_available
3. `patients` — cpf, name, birthdate, blood_type, allergies JSON, emergency_contact
4. `appointments` — patient_id, doctor_id, room_id, scheduled_at, duration_minutes, type, status, notes
5. `rooms` — name, type, capacity, floor, is_active
6. `payments` — appointment_id, amount, method, status, paid_at, receipt_number
7. `expenses` — category, description, amount, expense_date, paid_by user_id
8. `departments` — name, description, head_doctor_id FK
9. `insurance_companies` — name, cnpj, contact_email, coverage_types JSON, is_active
10. `events` — title, description, starts_at, ends_at, type, color, created_by
11. `messages` — sender_id, receiver_id, body, read_at, type
12. `notifications` — user_id, type, title, body, data JSON, read_at

Tabelas — CONTROLE DE ACESSO (Spatie após install):
13. `roles` — + coluna extra: `level` tinyint (para sistema de menus)
14. `permissions` — + coluna extra: `module` string (para agrupamento na UI)
15. `model_has_roles`, `model_has_permissions`, `role_has_permissions`

Tabelas — SISTEMA:
16. `menu_items` — label, route, icon, group, min_level, is_visible, order
17. `system_settings` — key unique, value, type, label, description
18. `audits` — gerada pelo owen-it automaticamente

Ao final incluir:
- ERD em ASCII com todas as relações
- Índices recomendados para performance
- Ordem de execução dos seeders

---

## TAREFA 4 — docs/04-api-routes.md

### Grupo A — Web Routes (Livewire + sessão, prefixo `/admin`)

Documentar todas as rotas dos grupos:

```
/admin/dashboard
/admin/appointments     /admin/appointments/{id}
/admin/doctors          /admin/doctors/{id}
/admin/patients         /admin/patients/{id}
/admin/rooms
/admin/payments
/admin/expenses
/admin/departments
/admin/insurance
/admin/events
/admin/chat

— CONTROLE DE ACESSO
/admin/usuarios
/admin/papeis
/admin/permissoes
/admin/vinculo-usuario

— SISTEMA
/admin/sistema/auditoria
/admin/sistema/menus
/admin/sistema/configuracoes
```

Para cada rota: Método | URI | Componente Livewire | Middleware | Named route

### Grupo B — API Routes (Sanctum, prefixo `/api/v1`)

Documentar auth + CRUD de cada módulo.
Para cada endpoint: Método | URI | Controller@method | Middleware | Request | Response (com exemplos JSON).

---

## TAREFA 5 — docs/05-components.md

Formato padrão para todos os componentes:

```markdown
### NomeDoComponente

Arquivo: app/Livewire/Grupo/NomeDoComponente.php
View: resources/views/livewire/grupo/nome.blade.php
Responsabilidade: (1 frase)

Props públicas: tabela
Computed: lista
Métodos: tabela (método | visibilidade | descrição | permissão)
Eventos emitidos / ouvidos: tabela
Alpine.js: sim/não — para quê
Permissão para renderizar: permission:xxx
```

Componentes — Dashboard: StatsCards, DoctorOnDuty, SurveyChart, MiniCalendar

Componentes — Clínica: AppointmentTable, AppointmentForm, DoctorTable, DoctorForm,
PatientTable, PatientForm, PaymentModal

Componentes — Controle de Acesso:
1. `AccessControl\UserTable` — listagem paginada com busca e filtros
2. `AccessControl\UserForm` — criar/editar + atribuir papéis
3. `AccessControl\RoleTable` — listagem de papéis
4. `AccessControl\RoleForm` — criar/editar + multi-select de permissões agrupadas
5. `AccessControl\PermissionTable` — listagem agrupada por módulo
6. `AccessControl\PermissionForm` — criar permissão com módulo
7. `AccessControl\UserRoleBinding` — dois painéis: usuário + checkboxes de papéis

Componentes — Sistema:
8. `System\AuditTable` — tabela com filtros entidade/ação/date range, exportar JSON
9. `System\AuditDiffModal` — modal Alpine.js com diff JSON Antes/Depois formatado
10. `System\MenuManager` — tabela com toggle visível + dropdown nível mínimo (salva ao alterar)
11. `System\SettingsTable` — edição inline: toggle para boolean, input para numérico/texto

Componentes — Shared:
12. `Shared\Sidebar` — lê menu_items do cache, filtra por papel, dark mode
13. `Shared\Topbar` — busca global, dark/light, notificações, idioma, avatar
14. `Shared\NotificationBell` — dropdown notificações não lidas
15. `Shared\DarkModeToggle` — alterna classe `dark` no `<html>` + localStorage
16. `Shared\ConfirmModal` — modal confirmação reutilizável (Alpine.js)
17. `Shared\FlashMessage` — flash verde/vermelho auto-dismiss 3s

---

## TAREFA 6 — docs/06-design-system.md

```markdown
# Design System — app-clinica-jm

## 6.1 Paleta de cores

  Modo claro:
  --primary:    #10B981 (green-500)   — botões, ativo, badges
  --secondary:  #F59E0B (amber-400)   — destaques, gráfico OPD
  --sidebar-bg: #1E3A5F               — fundo sidebar
  --danger:     #EF4444 (red-500)
  --info:       #3B82F6 (blue-500)
  --surface:    #FFFFFF               — cards, tabelas
  --bg:         #F1F5F9 (slate-100)
  --text:       #1E293B (slate-800)
  --muted:      #64748B (slate-500)

  Modo escuro:
  --surface:    #1E293B (slate-800)
  --bg:         #0F172A (slate-900)
  --text:       #F1F5F9 (slate-100)
  --sidebar-bg: #0F172A

## 6.2 Tipografia
  Fonte: Inter (Google Fonts)
  Escala: text-xs(12) / text-sm(14) / text-base(16) / text-lg(18) / text-xl(20) / text-2xl(24)
  Pesos: font-normal(400), font-medium(500), font-semibold(600)

## 6.3 Componentes base (classes Tailwind)

  Card padrão:
    bg-white dark:bg-slate-800 rounded-xl shadow-sm
    border border-slate-200 dark:border-slate-700 p-6

  KPI Card:
    (card padrão) + flex items-center justify-between

  Btn primário:
    bg-green-500 hover:bg-green-600 text-white font-medium px-4 py-2 rounded-lg transition

  Btn secundário:
    bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-2 rounded-lg

  Btn danger:
    bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg

  Input / Select / Textarea:
    w-full border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm
    focus:ring-2 focus:ring-green-500 bg-white dark:bg-slate-700

  Toggle (boolean settings):
    Ativado: bg-green-500  |  Desativado: bg-slate-300
    Implementado com Alpine.js x-bind:class

  Badges status appointment:
    agendado:  bg-blue-100 text-blue-800 rounded-full px-2.5 py-0.5 text-xs font-medium
    confirmado: bg-green-100 text-green-800 ...
    cancelado:  bg-red-100 text-red-800 ...
    realizado:  bg-purple-100 text-purple-800 ...

  Badge visível (menus):   bg-green-100 text-green-800 rounded-full ...
  Badge oculto (menus):    bg-slate-100 text-slate-500 rounded-full ...

  Tabela — header:  text-xs font-medium text-slate-500 uppercase tracking-wider
  Tabela — row:     hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-100

## 6.4 Layout base (diagrama ASCII)

  ┌─────────────────────────────────────────────────────────┐
  │  SIDEBAR (w-64, fixo)  │  TOPBAR (h-16)                 │
  │  ┌──────────────────┐  │  ┌─────────────────────────┐  │
  │  │ Logo / Nome      │  │  │ Busca | 🌙 🔔 BR | Avatar│  │
  │  ├──────────────────┤  │  └─────────────────────────┘  │
  │  │ CLÍNICA          │  │                                │
  │  │  ○ Dashboard     │  │  CONTENT AREA (flex-1)         │
  │  │  ○ Appointments  │  │  ┌─────────────────────────┐  │
  │  │  ○ Doctors       │  │  │ SISTEMA > Auditoria       │  │
  │  │  ...             │  │  │ H1: Auditoria             │  │
  │  │                  │  │  │                           │  │
  │  │ CONTROLE ACESSO  │  │  │  [filtros]                │  │
  │  │  ○ Usuários      │  │  │  [tabela paginada]        │  │
  │  │  ○ Papéis        │  │  │                           │  │
  │  │  ○ Permissões    │  │  └─────────────────────────┘  │
  │  │  ○ Vinc. Usuário │  │                                │
  │  │                  │  │                                │
  │  │ SISTEMA          │  │                                │
  │  │  ○ Auditoria ←   │  │                                │
  │  │  ○ Menus         │  │                                │
  │  │  ○ Configurações │  │                                │
  │  ├──────────────────┤  │                                │
  │  │ 🌐 Ver Loja      │  │                                │
  └──┴──────────────────┴──┴────────────────────────────────┘

## 6.5 Sidebar
  - w-64 fixo no desktop; collapse com Alpine.js no mobile
  - Grupos: text-xs uppercase font-semibold text-slate-400 tracking-wider px-3 mt-4 mb-1
  - Item normal: flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-slate-700
  - Item ativo: bg-green-500/20 text-green-400 font-medium border-r-2 border-green-500
  - Ícones: Heroicons outline (blade-heroicons)

## 6.6 Dark mode
  - Toggle: alterna classList.toggle('dark') no <html>
  - Persiste em localStorage
  - Componente: <livewire:shared.dark-mode-toggle />

## 6.7 Ícones
  - blade-ui-kit/blade-heroicons v2
  - <x-heroicon-o-calendar class="w-5 h-5" />

## 6.8 Padrão visual para módulos CRUD (Auditoria, Menus, Configurações)

  Cabeçalho:
    Label SISTEMA (uppercase muted text-xs)
    H1 "Auditoria" / "Configurações Gerais"

  Auditoria:
    Barra de filtros (dropdowns + date range + botões)
    Tabela sem border arredondado — clean, flat
    "Nenhum registro encontrado." centralizado

  Menus:
    Sem título de seção extra
    Texto descritivo acima da tabela
    Toggle verde/cinza — salva ao clicar

  Configurações:
    Lista vertical de settings
    Label grande + chave em cinza menor (text-slate-400 text-sm)
    Controle alinhado à direita
    Cada setting ocupa uma linha com border-b

## 6.9 Animações
  - wire:loading em botões de submit (spinner ou opacity-50)
  - Alpine x-transition em modais e dropdowns
  - Skeleton loading nas tabelas durante carregamento
  - FlashMessage: auto-dismiss 3s com Alpine setTimeout
```

---

## TAREFA 7 — docs/07-setup.md

```markdown
# Setup — Como rodar o projeto localmente

## Pré-requisitos
  PHP 8.2+, Composer 2.x, Node 20+, MySQL 8.0, Git

## Passo 1 — Clonar e instalar PHP
## Passo 2 — Configurar .env (mostrar .env.example completo)
## Passo 3 — Banco de dados
## Passo 4 — Migrations e seeds (ordem obrigatória)
  php artisan migrate
  php artisan db:seed --class=RoleSeeder
  php artisan db:seed --class=PermissionSeeder
  php artisan db:seed --class=RolePermissionSeeder
  php artisan db:seed --class=MenuItemSeeder
  php artisan db:seed --class=SystemSettingSeeder
  php artisan db:seed --class=UserSeeder

## Passo 5 — JS e assets
  npm install && npm run dev

## Passo 6 — Servidor
  php artisan serve

## Passo 7 — Usuários de teste (seed)
  admin@clinica.com      / password  → papel: admin (nível 1)
  medico@clinica.com     / password  → papel: medico (nível 2)
  recepcao@clinica.com   / password  → papel: recepcionista (nível 3)
  financeiro@clinica.com / password  → papel: financeiro (nível 4)

## Passo 8 — Testes
  php artisan test
  ./vendor/bin/pest --coverage

## Comandos do dia a dia
  php artisan permission:cache-reset   ← sempre após alterar roles/permissões
  php artisan cache:clear              ← sidebar em cache
  php artisan config:cache
  php artisan route:cache
  npm run build                        ← build produção

## Troubleshooting
  - Sidebar vazia → php artisan cache:clear (menu_items em cache)
  - Permissão negada → php artisan permission:cache-reset
  - Livewire não reage → npm run dev (Vite parado)
  - Auditoria não grava → verificar Auditable trait no Model
  - Toggle configuração não salva → verificar wire:model.blur no componente
```

---

## CHECKLIST FINAL

Após criar todos os arquivos, exiba:

```
📁 docs/ criados:
  ✅ 01-project.md
  ✅ 02-architecture.md
  ✅ 03-database.md
  ✅ 04-api-routes.md
  ✅ 05-components.md
  ✅ 06-design-system.md
  ✅ 07-setup.md

📦 Grupos documentados:
  ✅ CLÍNICA           — 11 módulos
  ✅ CONTROLE ACESSO   — Usuários, Papéis, Permissões, Vínculo
  ✅ SISTEMA           — Auditoria, Menus, Configurações
  ✅ SHARED            — Sidebar dinâmica, Topbar, Dark mode

📦 Pacotes a instalar (após confirmação):
  composer require spatie/laravel-permission
  composer require owen-it/laravel-auditing
  composer require blade-ui-kit/blade-heroicons
  npm install chart.js
```

Depois pergunte:
> "Documentação completa ✅
> Posso iniciar o scaffolding agora? Confirme com 'pode começar' para eu criar:
> migrations, seeders (Role/Permission/MenuItem/SystemSetting/User),
> layouts Blade, componentes Livewire base e o sistema de menus dinâmico."

---

## REGRAS DO JOGO

1. Não crie nenhum arquivo fora de `docs/` até confirmação
2. Crie na ordem 01 → 07
3. Cada arquivo deve ser completo — sem placeholders
4. Em decisões de design, decida e justifique
5. Aponte inconsistências antes de documentar
6. Markdown limpo com tabelas alinhadas
7. Controle de Acesso e Sistema com mesma profundidade dos módulos clínicos

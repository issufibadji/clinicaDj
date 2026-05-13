# Documentação — App Clínica DR.João Mendes

Painel administrativo para clínica médica. Stack: Laravel 12 · Livewire 3 · Alpine.js · Tailwind CSS 3 · Spatie Permission v6.

---

## Índice

| Arquivo | Descrição |
| --- | --- |
| [01-project.md](01-project.md) | Visão geral, objetivos, papéis RBAC e módulos do MVP |
| [02-architecture.md](02-architecture.md) | Arquitetura de camadas, padrões e decisões técnicas |
| [03-database.md](03-database.md) | Modelo de dados, migrations e relacionamentos |
| [04-api-routes.md](04-api-routes.md) | Mapa de rotas, middlewares e convenções de URL |
| [05-components.md](05-components.md) | Livewire components e Blade components documentados |
| [06-design-system.md](06-design-system.md) | Tokens de design, paleta, tipografia e classes utilitárias |
| [07-setup.md](07-setup.md) | Instalação local passo a passo |
| [08-deployment.md](08-deployment.md) | Deploy para produção (servidor, variáveis, queue) |
| [09-rbac-implementation-plan.md](09-rbac-implementation-plan.md) | Plano de implementação RBAC em 17 fases (FASE 0–16) |
| [10-rbac-code-standards.md](10-rbac-code-standards.md) | Padrões de código, templates e checklists de revisão |

---

## Setup rápido

> Pré-requisitos: PHP 8.2+, Composer, Node 20+, MySQL/SQLite.
> Para instruções completas veja [07-setup.md](07-setup.md).

### FASE 0 — Verificar ambiente

```bash
php -v && composer -V && node -v && mysql --version
composer global require laravel/installer
```

### FASE 1 — Criar projeto

```bash
laravel new app-clinica-jm --breeze --stack=livewire --pest
cd app-clinica-jm
cp .env.example .env && php artisan key:generate
```

> Edite `.env` agora: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `SESSION_DRIVER=database`

```bash
php artisan session:table && php artisan migrate
```

### FASE 2 — Instalar pacotes PHP

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

composer require owen-it/laravel-auditing
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="config"
php artisan vendor:publish --provider="OwenIt\Auditing\AuditingServiceProvider" --tag="migrations"

composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
php artisan vendor:publish --provider="PragmaRX\Google2FALaravel\ServiceProvider"

composer require blade-ui-kit/blade-heroicons
php artisan vendor:publish --tag=blade-heroicons-config

php artisan migrate
```

### FASE 3 — Frontend

```bash
npm install -D @tailwindcss/forms @tailwindcss/typography
npm install chart.js
npm run build    # verificar sem erros
```

### FASE 4 — Migrations extras e Seeds

```bash
php artisan make:migration add_level_to_roles_table
php artisan make:migration add_module_to_permissions_table
php artisan make:migration add_2fa_columns_to_users_table
php artisan make:migration create_menu_items_table
php artisan make:migration create_system_settings_table
php artisan migrate

php artisan make:seeder RoleSeeder
php artisan make:seeder PermissionSeeder
php artisan make:seeder RolePermissionSeeder
php artisan make:seeder MenuItemSeeder
php artisan make:seeder SystemSettingSeeder
php artisan make:seeder UserSeeder

php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=MenuItemSeeder
php artisan db:seed --class=SystemSettingSeeder
php artisan db:seed --class=UserSeeder

php artisan permission:cache-reset
```

### FASE 5 — Rodar (3 terminais)

```bash
npm run dev           # terminal 1
php artisan serve     # terminal 2
php artisan queue:work # terminal 3
```

---

## Credenciais de teste

| Papel | E-mail | Senha |
| --- | --- | --- |
| Administrador | `admin@clinica.com` | `password` |
| Médico | `medico@clinica.com` | `password` |
| Recepcionista | `recepcao@clinica.com` | `password` |
| Financeiro | `financeiro@clinica.com` | `password` |

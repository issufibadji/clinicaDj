# Setup — Como rodar o projeto localmente

## Pré-requisitos

Certifique-se de ter as seguintes ferramentas instaladas antes de começar:

| Ferramenta | Versão mínima | Verificar |
|-----------|--------------|-----------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 20 LTS+ | `node -v` |
| npm | 10+ | `npm -v` |
| MySQL | 8.0+ | `mysql --version` |
| Git | 2.x | `git --version` |

**Extensões PHP obrigatórias:**

```bash
php -m | grep -E "pdo_mysql|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|fileinfo|gd"
```

Todas devem estar listadas. No Windows com XAMPP/Laragon, já estão habilitadas por padrão.

---

## Passo 1 — Clonar e instalar dependências PHP

```bash
# Clonar o repositório
git clone https://github.com/seu-usuario/app-clinica-jm.git
cd app-clinica-jm

# Instalar dependências PHP
composer install
```

---

## Passo 2 — Configurar .env

```bash
# Copiar o arquivo de exemplo
cp .env.example .env

# Gerar a chave da aplicação
php artisan key:generate
```

Abra o arquivo `.env` e configure as variáveis obrigatórias:

```dotenv
# ===========================================
# APLICAÇÃO
# ===========================================
APP_NAME="app-clinica-jm"
APP_ENV=local
APP_KEY=                          # gerado pelo key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=pt_BR
APP_TIMEZONE=America/Sao_Paulo

# ===========================================
# BANCO DE DADOS — PRODUÇÃO (MySQL)
# ===========================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinica_jm            # criar este banco antes
DB_USERNAME=root                  # seu usuário MySQL
DB_PASSWORD=                      # sua senha MySQL

# ===========================================
# BANCO DE DADOS — TESTES (SQLite em memória)
# ===========================================
# (configurado em phpunit.xml, não precisa mudar aqui)

# ===========================================
# CACHE E SESSÃO
# ===========================================
CACHE_STORE=file                  # mude para redis em produção
SESSION_DRIVER=file               # mude para redis em produção
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync             # mude para redis em produção

# ===========================================
# EMAIL
# ===========================================
MAIL_MAILER=log                   # 'log' para dev (emails no storage/logs)
MAIL_FROM_ADDRESS="noreply@clinica.local"
MAIL_FROM_NAME="${APP_NAME}"

# Em produção, configure SMTP:
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.seu-provedor.com
# MAIL_PORT=587
# MAIL_USERNAME=seu@email.com
# MAIL_PASSWORD=sua-senha
# MAIL_ENCRYPTION=tls

# ===========================================
# STORAGE
# ===========================================
FILESYSTEM_DISK=local

# Em produção com S3:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=

# ===========================================
# LIVEWIRE
# ===========================================
LIVEWIRE_UPLOAD_TTL=300

# ===========================================
# SPATIE PERMISSION (padrão — não alterar)
# ===========================================
# Configurado em config/permission.php

# ===========================================
# AUDITORIA (owen-it)
# ===========================================
AUDIT_DRIVER=database
AUDIT_THRESHOLD=500               # máximo de registros por entidade

# ===========================================
# VITE
# ===========================================
VITE_APP_NAME="${APP_NAME}"
```

---

## Passo 3 — Banco de dados

### Criar o banco MySQL

```bash
# Via linha de comando
mysql -u root -p -e "CREATE DATABASE clinica_jm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Ou via MySQL Workbench / phpMyAdmin / DBeaver
# Criar banco: clinica_jm
# Charset: utf8mb4
# Collation: utf8mb4_unicode_ci
```

---

## Passo 4 — Migrations e seeds

```bash
# Executar todas as migrations
php artisan migrate

# Verificar se as migrations rodaram corretamente
php artisan migrate:status
```

---

## Passo 5 — Instalar dependências JS e compilar

```bash
# Instalar dependências JavaScript
npm install

# Compilar assets para desenvolvimento (com hot reload)
npm run dev

# Ou compilar para produção (uma vez)
npm run build
```

---

## Passo 6 — Rodar o servidor

Abra **dois terminais**:

**Terminal 1 — Laravel:**
```bash
php artisan serve
# Servidor em: http://localhost:8000
```

**Terminal 2 — Vite (hot reload):**
```bash
npm run dev
# Vite em: http://localhost:5173
```

Acesse: [http://localhost:8000](http://localhost:8000)

### Alternativa: Laragon (Windows)

Se usar Laragon ou XAMPP, configure um virtual host apontando para `app-clinica-jm/public` e acesse via domínio customizado (ex: `http://clinica.test`).

---

## Passo 7 — Usuários de teste (seed)

```bash
# Rodar todos os seeders (cria dados de demonstração)
php artisan db:seed

# Ou rodar fresh (apaga tudo e recria)
php artisan migrate:fresh --seed
```

### Credenciais de acesso por papel:

| Email | Senha | Papel | Acesso |
|-------|-------|-------|--------|
| admin@clinica.dev | password | Administrador | Total |
| medico@clinica.dev | password | Médico | Agenda, prontuários, chat |
| recepcionista@clinica.dev | password | Recepcionista | Agendamentos, pacientes |
| financeiro@clinica.dev | password | Financeiro | Pagamentos, despesas |

**Dados de demonstração gerados pelo seeder:**
- 4 usuários (1 por papel)
- 10 médicos com especialidades variadas
- 50 pacientes com dados fictícios (Faker)
- 200 agendamentos distribuídos nos últimos 60 dias e próximos 30
- Pagamentos para agendamentos realizados
- Despesas dos últimos 6 meses
- Eventos para os próximos 30 dias
- Histórico de mensagens de chat

---

## Passo 8 — Rodar testes

```bash
# Rodar todos os testes (usa SQLite in-memory automaticamente)
php artisan test

# Rodar com cobertura de código (requer Xdebug ou PCOV)
php artisan test --coverage --min=80

# Rodar apenas testes unitários
php artisan test --testsuite=Unit

# Rodar apenas testes de feature
php artisan test --testsuite=Feature

# Rodar um arquivo específico
php artisan test tests/Feature/Appointments/AppointmentCrudTest.php

# Rodar com output detalhado
php artisan test --verbose
```

---

## Comandos úteis do dia a dia

```bash
# === DESENVOLVIMENTO ===

# Limpar todos os caches
php artisan optimize:clear

# Regenerar autoload e caches de rota/config/view
php artisan optimize

# Listar todas as rotas registradas
php artisan route:list

# Listar componentes Livewire registrados
php artisan livewire:list

# Abrir REPL interativo (Tinker)
php artisan tinker

# === BANCO DE DADOS ===

# Nova migration
php artisan make:migration create_nome_table

# Rollback da última migration
php artisan migrate:rollback

# Refresh completo (apaga e recria tudo) com seeds
php artisan migrate:fresh --seed

# Rodar apenas um seeder
php artisan db:seed --class=DoctorSeeder

# === LIVEWIRE ===

# Criar novo componente Livewire
php artisan make:livewire NomeDoComponente

# Publicar config do Livewire
php artisan livewire:publish --config

# === CÓDIGO ===

# Formatar código com Laravel Pint
./vendor/bin/pint

# Verificar padrões sem modificar
./vendor/bin/pint --test

# Análise estática com PHPStan (nível 5)
./vendor/bin/phpstan analyse

# === ASSETS ===

# Build de produção
npm run build

# Verificar se há atualizações nos pacotes PHP
composer outdated

# Verificar vulnerabilidades nas dependências PHP
composer audit

# === PERMISSÕES (Spatie) ===

# Limpar cache de permissões após alteração
php artisan permission:cache-reset

# === LOGS ===

# Acompanhar logs em tempo real (Linux/Mac)
tail -f storage/logs/laravel.log

# No Windows (PowerShell)
Get-Content storage\logs\laravel.log -Wait -Tail 50

# === QUEUE (quando mudar de sync para redis) ===

# Iniciar worker de fila
php artisan queue:work

# Monitorar filas com Horizon
php artisan horizon
```

---

## Troubleshooting comum

### ❌ "SQLSTATE[HY000] [1049] Unknown database"

**Causa:** O banco de dados não foi criado.

```bash
mysql -u root -p -e "CREATE DATABASE clinica_jm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

### ❌ "Class X not found" após instalar pacote

**Causa:** Autoload desatualizado.

```bash
composer dump-autoload
```

---

### ❌ Página em branco ou erro 500 sem mensagem

**Causa:** APP_DEBUG está desativado ou erros de permissão de arquivo.

```bash
# Ativar debug temporariamente
# No .env: APP_DEBUG=true

# Verificar permissões (Linux/Mac)
chmod -R 775 storage bootstrap/cache

# No Windows, verificar que o usuário do PHP tem acesso à pasta
```

---

### ❌ "Vite manifest not found"

**Causa:** Assets não foram compilados.

```bash
npm install && npm run build
```

---

### ❌ "The payload is invalid" em operações Livewire

**Causa:** APP_KEY mudou ou cache de sessão desatualizado.

```bash
php artisan key:generate
php artisan optimize:clear
```

---

### ❌ Emails não são enviados

**Causa:** Em desenvolvimento, MAIL_MAILER=log. Os emails ficam em:

```bash
# Ver emails "enviados" (guardados no log)
tail -100 storage/logs/laravel.log | grep -A 20 "Message-ID:"
```

Para testar emails reais localmente, use [Mailpit](https://github.com/axllent/mailpit):

```bash
# .env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
```

---

### ❌ "Permission denied" ao usar Spatie após criar usuário

**Causa:** Cache de permissões do Spatie não foi atualizado.

```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

---

### ❌ Componente Livewire não atualiza após mudança no PHP

**Causa:** Cache de views Blade.

```bash
php artisan view:clear
php artisan livewire:purge
```

---

### ❌ Tests falhando com "no such table"

**Causa:** `phpunit.xml` não está configurando SQLite in-memory.

Verifique se `phpunit.xml` contém:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

### ✅ Verificar que tudo está funcionando

```bash
# Checklist rápido de saúde
php artisan about
php artisan route:list --compact
php artisan migrate:status
php artisan test --stop-on-failure
```

# Guia de Deploy

## Pre-requisitos de Producao

| Requisito | Versao Minima |
|---|---|
| PHP | 8.1 |
| MySQL | 8.0 |
| Composer | 2.x |
| Node.js | 18.x (apenas para build) |
| Nginx ou Apache | Qualquer versao recente |

**Extensoes PHP necessarias:**
```
php-fpm, php-mysql, php-mbstring, php-xml,
php-bcmath, php-curl, php-zip, php-gd
```

---

## 1. Deploy em VPS (Ubuntu/Debian com Nginx)

### 1.1 Preparar o servidor

```bash
# Atualizar o sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias
sudo apt install -y nginx mysql-server php8.1-fpm \
  php8.1-mysql php8.1-mbstring php8.1-xml \
  php8.1-bcmath php8.1-curl php8.1-zip php8.1-gd \
  composer git unzip

# Instalar Node.js (apenas para build, pode remover apos)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 1.2 Configurar o banco de dados

```bash
sudo mysql_secure_installation

sudo mysql -u root -p
```

```sql
CREATE DATABASE portfolio_web_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'senha_forte_aqui';
GRANT ALL PRIVILEGES ON portfolio_web_laravel.* TO 'portfolio_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 1.3 Fazer o deploy da aplicacao

```bash
# Clonar o repositorio
cd /var/www
sudo git clone https://github.com/seu-usuario/seu-repo.git portfolio
sudo chown -R www-data:www-data portfolio
cd portfolio

# Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# Configurar o ambiente
cp .env.example .env
php artisan key:generate
```

### 1.4 Configurar o `.env` de producao

```env
APP_NAME="Meu Portfolio"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://meusite.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portfolio_web_laravel
DB_USERNAME=portfolio_user
DB_PASSWORD=senha_forte_aqui

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu@gmail.com
MAIL_PASSWORD=sua_senha_app_google
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=seu@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 1.5 Finalizar a configuracao

```bash
# Compilar assets frontend
npm install
npm run build

# Executar migrations e seeders
php artisan migrate --force
php artisan db:seed --force

# Criar link simbolico para storage
php artisan storage:link

# Otimizacoes para producao
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Permissoes corretas
sudo chown -R www-data:www-data /var/www/portfolio
sudo chmod -R 755 /var/www/portfolio/storage
sudo chmod -R 755 /var/www/portfolio/bootstrap/cache
```

### 1.6 Configurar Nginx

Criar o arquivo de configuracao:

```bash
sudo nano /etc/nginx/sites-available/portfolio
```

```nginx
server {
    listen 80;
    server_name meusite.com www.meusite.com;
    root /var/www/portfolio/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/portfolio /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 1.7 SSL com Let's Encrypt (HTTPS)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d meusite.com -d www.meusite.com
sudo systemctl reload nginx
```

---

## 2. Deploy em Shared Hosting (cPanel)

### 2.1 Via File Manager ou FTP

1. Faça o upload de todos os arquivos do projeto para uma pasta temporaria (ex: `portfolio_tmp/`)
2. Copie o conteudo da pasta `public/` para `public_html/`
3. Copie todos os outros arquivos para uma pasta acima de `public_html/` (ex: `portfolio/`)

### 2.2 Ajustar `public/index.php`

```php
// Alterar os caminhos para apontar para a pasta correta
require __DIR__.'/../portfolio/vendor/autoload.php';
$app = require_once __DIR__.'/../portfolio/bootstrap/app.php';
```

### 2.3 Via Terminal SSH (se disponivel)

```bash
cd ~/portfolio
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Editar .env com as credenciais do banco cPanel
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm install && npm run build
php artisan storage:link
php artisan optimize
```

---

## 3. Deploy com Docker

### `Dockerfile`

```dockerfile
FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader \
    && php artisan key:generate \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### `docker-compose.yml`

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=production
      - DB_HOST=db
      - DB_DATABASE=portfolio
      - DB_USERNAME=root
      - DB_PASSWORD=secret
    depends_on:
      - db
    volumes:
      - uploads:/var/www/public/uploads

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: portfolio
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:
  uploads:
```

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
```

---

## 4. Checklist de Deploy

Antes de ir para producao, verificar:

- [ ] `APP_DEBUG=false` no `.env`
- [ ] `APP_ENV=production` no `.env`
- [ ] `APP_URL` configurado com o dominio real
- [ ] Banco de dados configurado e acessivel
- [ ] Migrations executadas (`php artisan migrate --force`)
- [ ] Seeders executados (`php artisan db:seed --force`)
- [ ] Assets compilados (`npm run build`)
- [ ] Storage link criado (`php artisan storage:link`)
- [ ] Caches gerados (`php artisan optimize`)
- [ ] Permissoes de `storage/` e `bootstrap/cache/` configuradas (755)
- [ ] HTTPS configurado (SSL)
- [ ] E-mail configurado e testado
- [ ] Senha do admin alterada do padrao

---

## 5. Atualizacoes em Producao

```bash
# Ativar modo de manutencao
php artisan down

# Baixar atualizacoes
git pull origin master

# Instalar dependencias atualizadas
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Executar novas migrations
php artisan migrate --force

# Limpar e regenerar caches
php artisan optimize:clear
php artisan optimize

# Voltar ao ar
php artisan up
```

---

## 6. Monitoramento e Logs

```bash
# Ver logs da aplicacao
tail -f storage/logs/laravel.log

# Limpar logs
php artisan log:clear   # (se instalado o pacote)
# ou manualmente:
truncate -s 0 storage/logs/laravel.log

# Jobs falhados
php artisan queue:failed
php artisan queue:flush   # Limpa jobs falhados
```

**Verificar saude do sistema:**
```bash
php artisan about           # Info do ambiente Laravel
php artisan config:show db  # Configuracoes do banco
```

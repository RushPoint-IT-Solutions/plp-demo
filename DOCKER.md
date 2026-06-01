# PLP Demo - Docker Setup Guide

## Requirements

| Tool | Minimum Version |
|------|-----------------|
| Docker | 24.x |
| Docker Compose | v2, bundled with Docker |
| Git | any |

---

## 1. Install Docker on the Linux Server

```bash
# Ubuntu / Debian
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker

# Verify
docker --version
docker compose version
```

Install Git if it is not yet installed:

```bash
sudo apt update
sudo apt install -y git
```

---

## 2. Create a GitHub Personal Access Token (PAT)

If the repository is private, create a PAT so the server can clone and pull updates.

1. Go to GitHub -> Settings -> Developer settings -> Personal access tokens -> Tokens (classic)
   - Direct link: `https://github.com/settings/tokens`
2. Click `Generate new token (classic)`
3. Set a name, for example `plp-server-deploy`
4. Set expiration based on your policy
5. Select scope: `repo`
6. Click `Generate token` and copy it immediately

If the repository is public, you can skip this step.

---

## 3. Clone the Repository

For a private repository, use your GitHub username and PAT:

```bash
# Replace YOUR_USERNAME, YOUR_TOKEN, and YOUR_REPOSITORY_URL
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
git clone https://YOUR_USERNAME:YOUR_TOKEN@github.com/YOUR_ORG/YOUR_REPOSITORY.git /var/www/plp-demo
cd /var/www/plp-demo
```

For a public repository:

```bash
sudo mkdir -p /var/www
sudo chown -R $USER:$USER /var/www
git clone https://github.com/YOUR_ORG/YOUR_REPOSITORY.git /var/www/plp-demo
cd /var/www/plp-demo
```

---

## 4. Configure Environment

```bash
cp .env.docker .env
nano .env
```

Update these required values:

```env
APP_URL=http://YOUR_SERVER_IP:8080
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=plp_demo
DB_USERNAME=plp_user
DB_PASSWORD=plp_password
```

Generate `APP_KEY` after containers are running:

```bash
docker compose exec app php artisan key:generate --force
```

Mail defaults to log mode in `.env.docker`. For production mail, update:

```env
MAIL_MAILER=smtp
MAIL_DRIVER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
```

If you change database credentials in `.env`, also update the matching values in `docker-compose.yml` under the `app` and `mysql` services.

---

## 5. Build and Start Containers

```bash
cd /var/www/plp-demo
docker compose up -d --build
```

This starts:

| Container | Role | External Port |
|-----------|------|---------------|
| `plp-demo-web` | Nginx | 8080 |
| `plp-demo-app` | PHP 7.4-FPM | internal |
| `plp-demo-mysql` | MySQL 5.7 | 3307 |
| `plp-demo-phpmyadmin` | phpMyAdmin | 8081 |
| `plp-demo-node` | Node 14 asset builder | profile only |

---

## 6. Run First-Time Setup

```bash
# Generate app key if APP_KEY is blank
docker compose exec app php artisan key:generate --force

# Install PHP dependencies if needed
docker compose exec app composer install --no-dev --optimize-autoloader

# Run all migrations
docker compose exec app php artisan migrate --force

# Create storage symlink
docker compose exec app php artisan storage:link

# Clear caches
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
```

Optional seed:

```bash
docker compose exec app php artisan db:seed --force
```

App is now live at:

```text
http://YOUR_SERVER_IP:8080
```

---

## 7. Optional: Expose on Port 80 with a Domain

If you want to serve directly on port 80, change the web port in `docker-compose.yml`:

```yaml
web:
  ports:
    - "80:80"
```

For SSL, use Nginx, Apache, Caddy, or a load balancer in front of port `8080`, then enable HTTPS there.

Do not expose phpMyAdmin publicly on production servers.

---

## 8. Daily Commands

| Task | Command |
|------|---------|
| Start | `docker compose up -d` |
| Stop | `docker compose down` |
| Restart | `docker compose restart` |
| Rebuild | `docker compose up -d --build` |
| Tail logs | `docker compose logs -f` |
| App shell | `docker compose exec app bash` |
| Run artisan | `docker compose exec app php artisan <cmd>` |
| Run composer | `docker compose exec app composer <cmd>` |
| Build assets | `docker compose --profile assets run --rm node npm run dev` |

---

## 9. Update System from Git

This project currently uses manual Git updates from the server shell.

```bash
cd /var/www/plp-demo
git pull
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose restart app
```

If the repository is private, make sure the server remote uses either:

```bash
git remote set-url origin https://YOUR_USERNAME:YOUR_TOKEN@github.com/YOUR_ORG/YOUR_REPOSITORY.git
```

or SSH deploy keys.

If Git reports an unsafe repository:

```bash
docker compose exec app git config --system --add safe.directory /var/www/html
```

---

## 10. Database Access

Connect with any MySQL client from the server or through an SSH tunnel.

| Field | Value |
|-------|-------|
| Host | `127.0.0.1` |
| Port | `3307` |
| Database | `plp_demo` |
| Username | `plp_user` |
| Password | `plp_password` |

SSH tunnel from your local machine:

```bash
ssh -L 3307:127.0.0.1:3307 user@YOUR_SERVER_IP
```

Then connect your database client to:

```text
127.0.0.1:3307
```

phpMyAdmin is available at:

```text
http://YOUR_SERVER_IP:8081
```

---

## 11. Reset / Rebuild

### Rebuild after Dockerfile changes

```bash
docker compose up -d --build
```

### Full reset, destroys database

This is irreversible.

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

---

## 12. Troubleshooting

### Permission denied on storage/

```bash
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

### git pull fails: fatal unsafe repository

```bash
docker compose exec app git config --system --add safe.directory /var/www/html
```

### git pull fails: Authentication failed

- Confirm the PAT has `repo` scope and has not expired
- Confirm the remote URL is correct:

```bash
git remote -v
```

### Cannot connect to database after first up

MySQL can take around 15 seconds to initialize on first start. Wait, then run:

```bash
docker compose exec app php artisan migrate --force
```

### Port 8080 already in use

Edit `docker-compose.yml`:

```yaml
web:
  ports:
    - "9090:80"
```

Then run:

```bash
docker compose up -d
```

### Clear all Laravel caches

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
```

---

## 13. File Structure (Docker-specific)

```text
/var/www/plp-demo/
├── Dockerfile                  # PHP 7.4-FPM image
├── docker-compose.yml          # Services: web, app, mysql, phpMyAdmin, optional node
├── .env                        # Active server env, copied from .env.docker
├── .env.docker                 # Docker env template
├── .dockerignore
├── docker/
│   ├── nginx/
│   │   └── default.conf        # Nginx server block, public/ docroot
│   └── php/
│       └── entrypoint.sh       # Laravel bootstrapping script
└── DOCKER.md                   # This file
```

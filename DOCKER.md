# Docker Setup

This stack is for local development of the Laravel 5.7 system.

## Services

- App: PHP 7.4 + Apache at `http://localhost:8080`
- MySQL 5.7 at host port `3307`
- phpMyAdmin at `http://localhost:8081`
- Optional Node 14 asset builder

## Fresh Server Setup From Git

Install Docker and Git on the server first.

Ubuntu/Debian example:

```bash
sudo apt update
sudo apt install -y git ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo usermod -aG docker "$USER"
```

Log out and back in after adding your user to the `docker` group.

Clone the project:

```bash
cd /var/www
git clone <YOUR_REPOSITORY_URL> plp-demo
cd plp-demo
```

Create the Docker environment file:

```bash
cp .env.docker .env
```

Edit `.env` for the server domain and database password:

```bash
nano .env
```

At minimum, update:

```env
APP_URL=http://your-server-domain-or-ip:8080
DB_HOST=mysql
DB_DATABASE=plp_demo
DB_USERNAME=plp_user
DB_PASSWORD=change_this_password
```

If you change the database password in `.env`, also update `docker-compose.yml` under the `mysql` and `app` service environment values.

Build and start:

```bash
docker compose up -d --build
```

Install/check dependencies and prepare Laravel:

```bash
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

Optional seed:

```bash
docker compose exec app php artisan db:seed --force
```

Open:

```text
App:        http://your-server-domain-or-ip:8080
phpMyAdmin: http://your-server-domain-or-ip:8081
```

For a public server, put Nginx or Apache reverse proxy in front of `localhost:8080`, enable HTTPS, and do not expose phpMyAdmin publicly.

## Start

```bash
docker compose up -d --build
```

## First Run

If your host `.env` still points to XAMPP/MySQL on `127.0.0.1`, either copy the Docker env:

```bash
copy .env.docker .env
```

or keep your `.env` and rely on the Docker Compose environment overrides.

Then run migrations:

```bash
docker compose exec app php artisan migrate
```

Optional seed:

```bash
docker compose exec app php artisan db:seed
```

## Common Commands

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app composer install
docker compose --profile assets run --rm node npm run dev
```

## Database Login

- Host from your computer: `127.0.0.1`
- Port: `3307`
- Database: `plp_demo`
- User: `plp_user`
- Password: `plp_password`
- Root password: `root_password`

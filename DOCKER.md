# Docker Setup

This stack is for local development of the Laravel 5.7 system.

## Services

- App: PHP 7.4 + Apache at `http://localhost:8080`
- MySQL 5.7 at host port `3307`
- phpMyAdmin at `http://localhost:8081`
- Optional Node 14 asset builder

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

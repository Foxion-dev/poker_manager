# Разработка

## Локальный запуск без Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
# Настроить .env (DB_* — локальная MySQL)
php artisan migrate
npm install
```

В одном терминале:

```bash
php artisan serve
```

В другом:

```bash
npm run dev
```

Приложение: http://localhost:8000 (или порт из `php artisan serve`). Vite отдаёт assets с hot reload.

## Локальный запуск с Docker (Makefile)

Используются `docker-compose.dev.yml` и Makefile.

Первая настройка (один раз):

```bash
make setup-dev
```

Запуск в dev-режиме:

```bash
make start
```

Приложение: http://localhost:8080, Vite: http://localhost:5173.

Остановка контейнеров: `make down`.

## Команды Makefile (локальная разработка)

| Команда | Описание |
|---------|----------|
| `make help` | Список команд |
| `make start` | Запуск в dev-режиме (контейнеры + Vite) |
| `make up` / `make down` | Запуск / остановка контейнеров |
| `make migrate` | Миграции |
| `make migrate-rollback` | Откат последней миграции |
| `make migrate-fresh` | Пересоздать БД и запустить миграции |
| `make seed` | Сидеры |
| `make setup-dev` | Первая настройка проекта |

Полный список: `make help`.

## Artisan (локально)

С Docker: зайти в контейнер и вызвать artisan, например:

```bash
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan telegram:webhook-info
```

Без Docker: `php artisan` из корня проекта.

## Структура проекта (кратко)

- `app/` — логика приложения (модели, контроллеры, сервисы).
- `app/Services/Telegram/` — команды и обработчики Telegram-бота.
- `routes/api.php` — API-маршруты.
- `resources/js/` — Vue 3, роутер, stores, компоненты.
- `database/migrations/`, `database/seeders/` — миграции и сидеры.

Админка: маршруты с префиксом `/admin`, middleware `admin`. Пользователь с `is_admin = true` получает доступ к пользователям, румам, валютам, настройкам Telegram.

# Документация для администратора и разработчика

Установка, настройка, деплой и разработка Poker Manager.

## Содержание

| Документ | Описание |
|----------|----------|
| [Установка и требования](installation.md) | Требования, окружение, миграции, сидеры |
| [Деплой](deployment.md) | Выкладка на сервер, GitHub Actions, Makefile |
| [Telegram-бот](telegram.md) | Настройка бота в админке, вебхук, устранение проблем |
| [Разработка](development.md) | Локальный запуск, Docker, Makefile, команды |

## Технологии

- **Backend:** Laravel 12, PHP 8.2+, MySQL
- **Frontend:** Vue 3, Vue Router, Pinia, Vite, Tailwind CSS
- **API:** REST, Laravel Sanctum
- **Деплой:** Docker (Laravel Sail), Nginx, GitHub Actions (опционально)

## Быстрый старт (разработка)

```bash
git clone <repo>
cd poker-manager
cp .env.example .env
# Настроить .env (DB_*, APP_URL)
composer install
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
# В другом терминале: npm run dev (Vite)
```

Либо через Docker: см. [Разработка](development.md).

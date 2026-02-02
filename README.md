# Poker Manager

Веб-приложение для учёта покерных турниров, паков и балансов по румам с интеграцией Telegram-бота.

## Возможности

- **Турниры** — учёт турниров по румам: байин, валюта, кэшаут, кэшаут баунти, ребаи.
- **Паки** — учёт паков (серий турниров) с датами и результатами.
- **Румы и балансы** — глобальные и личные румы, балансы в валюте рума.
- **Статистика** — прибыль/убыток, ITM, ROI, средний байин и кэшаут за выбранный период.
- **Локации** — клубы/точки с турнирами и участниками (для организаторов).
- **Telegram-бот** — привязка аккаунта, баланс по румам, статистика за период, добавление турнира пошагово.

## Стек

- Backend: Laravel 12, PHP 8.2+, MySQL
- Frontend: Vue 3, Vue Router, Pinia, Vite, Tailwind CSS
- API: REST, Laravel Sanctum

## Быстрый старт

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
```

В отдельном терминале для разработки с hot reload: `npm run dev`.

Либо через Docker: `make start` (см. [Документация для разработчика](docs/admin/development.md)).

## Документация

- **[Документация](docs/README.md)** — общий индекс.
- **[Для пользователей](docs/users/README.md)** — работа с приложением и Telegram-ботом.
- **[Для администратора и разработчика](docs/admin/README.md)** — установка, деплой, настройка бота, разработка.

Дополнительно в папке [docs/](docs/):

- [docs/DEPLOY.md](docs/DEPLOY.md) — деплой на сервер, GitHub Actions.
- [docs/TELEGRAM_BOT_SETUP.md](docs/TELEGRAM_BOT_SETUP.md) — настройка Telegram-бота на сервере.

## Лицензия

MIT

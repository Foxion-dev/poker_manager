# Установка и требования

## Требования

- PHP 8.2+
- Composer
- Node.js 18+ и npm
- MySQL 8 (или совместимая СУБД)
- Расширения PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

Для разработки через Docker достаточно Docker и Docker Compose (см. [Разработка](development.md)).

## Окружение

1. Скопируйте `.env.example` в `.env`:
   ```bash
   cp .env.example .env
   ```

2. Сгенерируйте ключ приложения:
   ```bash
   php artisan key:generate
   ```

3. Настройте в `.env`:
   - `APP_NAME` — название приложения
   - `APP_URL` — полный URL сайта (для Telegram вебхуков обязателен HTTPS на продакшене)
   - `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — подключение к БД
   - При необходимости: `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION`

## Установка зависимостей

```bash
composer install
npm install
```

## Миграции

```bash
php artisan migrate
```

При первом запуске или для чистой БД:

```bash
php artisan migrate:fresh
```

## Сидеры (тестовые данные)

```bash
php artisan db:seed
```

Сидер создаёт администратора и тестового пользователя (см. `database/seeders/DatabaseSeeder.php`). Для продакшена сидеры обычно не запускают или используют отдельные сидеры (валюты, румы и т.д.).

## Права доступа

Каталоги `storage` и `bootstrap/cache` должны быть доступны для записи веб-серверу:

```bash
chmod -R 775 storage bootstrap/cache
```

## Сборка фронтенда

Разработка (с hot reload):

```bash
npm run dev
```

Продакшен:

```bash
npm run build
```

После сборки в `public/build` появятся скомпилированные assets; Laravel подхватывает их через Vite manifest.

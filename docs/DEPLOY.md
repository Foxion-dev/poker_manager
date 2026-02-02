# Деплой на сервере

## Автодеплой (GitHub Actions)

При пуше в ветку `main` выполняется workflow `.github/workflows/deploy.yml`: SSH на сервер и выполнение `make deploy` в каталоге проекта.

**Секреты репозитория (Settings → Secrets and variables → Actions):**

| Секрет | Описание |
|--------|----------|
| `DEPLOY_HOST` | IP или hostname сервера |
| `DEPLOY_USER` | SSH-пользователь |
| `DEPLOY_SSH_KEY` | Приватный SSH-ключ |
| `DEPLOY_SSH_PASSPHRASE` | (опционально) Пароль от ключа |
| `DEPLOY_PORT` | (опционально) Порт SSH, по умолчанию 22 |

В `~/.ssh/authorized_keys` на сервере должен быть добавлен соответствующий публичный ключ.

## Команды на сервере

Каталог проекта на сервере — например `/home/poker_manager` (или свой путь). Перед первым деплоем выполните `composer install` и настройте `.env`.

### Полный деплой

```bash
cd /path/to/poker-manager
make deploy
```

Выполняется: сброс локальных изменений, `git pull`, установка PHP-зависимостей, сборка фронтенда, миграции, очистка и кеширование конфига/роутов/views.

### Лёгкий деплой (без composer и миграций)

```bash
make deploy-lite
```

Обновление кода и пересборка фронтенда. Используется в CI при пуше в `main`, если на сервере уже настроен проект.

### Первая настройка на сервере

Если проект на сервер ещё не разворачивали:

1. Клонировать репозиторий, перейти в каталог.
2. Выполнить `composer install` (без dev-зависимостей: `--no-dev` по желанию).
3. Скопировать `.env.example` в `.env`, задать `APP_KEY`, `APP_URL`, `DB_*`.
4. Запустить:

```bash
make setup
```

Либо выполнить шаги из раздела «Пошаговый деплой» ниже.

## Пошаговый деплой (без make)

Если `make deploy` не подходит, можно выполнить шаги вручную (на сервере используется Sail: `./vendor/bin/sail`).

### 1. Обновление кода

```bash
git pull origin main
```

### 2. Запуск контейнеров

```bash
./vendor/bin/sail up -d
sleep 5
```

### 3. PHP-зависимости

```bash
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader"
```

### 4. Права доступа

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"
```

### 5. Фронтенд

```bash
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && npm install && NODE_ENV=production npm run build"
```

### 6. Миграции

```bash
./vendor/bin/sail artisan migrate --force
```

### 7. Кеш

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache
```

## Миграции на сервере

Запуск миграций (если не используете полный `make deploy`):

```bash
./vendor/bin/sail artisan migrate --force
```

Откат последнего батча:

```bash
./vendor/bin/sail artisan migrate:rollback --step=1
```

## Решение проблем

### Vite manifest not found

Убедитесь, что сборка прошла успешно:

```bash
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && NODE_ENV=production npm run build"
./vendor/bin/sail exec laravel.test sh -c "ls -la /var/www/html/public/build/"
```

При необходимости создайте каталог и пересоберите:

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "mkdir -p /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build"
```

### Permission denied

Исправление прав:

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"
```

### Статус контейнеров и логи

```bash
./vendor/bin/sail ps
./vendor/bin/sail logs laravel.test
./vendor/bin/sail restart
```

### Проверка после деплоя

- Наличие манифеста:  
  `./vendor/bin/sail exec laravel.test sh -c "test -f /var/www/html/public/build/.vite/manifest.json && echo OK || test -f /var/www/html/public/build/manifest.json && echo OK"`
- Доступность сайта: `curl -I https://ваш-домен`
- Логи Laravel:  
  `./vendor/bin/sail exec laravel.test sh -c "tail -50 /var/www/html/storage/logs/laravel.log"`

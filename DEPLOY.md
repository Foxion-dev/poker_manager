# Инструкция по деплою на сервере

## Автодеплой через GitHub Actions

При пуше в ветку `main` запускается workflow `.github/workflows/deploy.yml`: по SSH подключается к серверу и выполняет `git pull` и `make deploy` в `/home/poker_manager`.

**Секреты в репозитории (Settings → Secrets and variables → Actions):**

| Секрет | Описание |
|--------|----------|
| `DEPLOY_HOST` | IP или hostname сервера |
| `DEPLOY_USER` | SSH-пользователь на сервере |
| `DEPLOY_SSH_KEY` | Приватный SSH-ключ для доступа к серверу |
| `DEPLOY_SSH_PASSPHRASE` | (если ключ с паролем) Пароль от приватного ключа |
| `DEPLOY_PORT` | (опционально) Порт SSH, по умолчанию 22 |

На сервере в `~/.ssh/authorized_keys` должен быть добавлен соответствующий публичный ключ.

## Быстрый деплой

На сервере выполните команду:

```bash
cd /home/poker_manager
make deploy
```

## Пошаговый процесс деплоя

Если `make deploy` не работает, выполните команды вручную:

### 1. Обновление кода из Git

```bash
cd /home/poker_manager
git pull origin main
```

### 2. Запуск контейнеров (если не запущены)

```bash
./vendor/bin/sail up -d
sleep 5
```

### 3. Установка PHP зависимостей

```bash
./vendor/bin/sail composer install --no-interaction --prefer-dist --optimize-autoloader
```

### 4. Исправление прав доступа

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"
```

### 5. Установка Node.js зависимостей

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "rm -f /var/www/html/package-lock.json"
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && npm install"
```

### 6. Создание директории для сборки

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "mkdir -p /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build && chmod -R 755 /var/www/html/public/build"
```

### 7. Сборка фронтенда (production)

```bash
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && NODE_ENV=production npm run build"
```

### 8. Проверка наличия манифеста

```bash
./vendor/bin/sail exec laravel.test sh -c "ls -lh /var/www/html/public/build/manifest.json"
```

Если файл не найден, проверьте логи сборки:

```bash
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && NODE_ENV=production npm run build 2>&1"
```

### 9. Исправление прав доступа после сборки

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 755 /var/www/html/public/build"
```

### 10. Запуск миграций

```bash
sleep 3
./vendor/bin/sail artisan migrate --force
```

### 11. Очистка кеша

```bash
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
```

### 12. Оптимизация приложения

```bash
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan view:cache
```

## Решение проблем

### Ошибка: Vite manifest not found

**Причина:** Файл `manifest.json` не был создан при сборке фронтенда.

**Решение:**

1. Проверьте, что сборка завершилась успешно:
   ```bash
   ./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && NODE_ENV=production npm run build 2>&1"
   ```

2. Проверьте наличие директории и файлов:
   ```bash
   ./vendor/bin/sail exec laravel.test sh -c "ls -la /var/www/html/public/build/"
   ```

3. Если директория не существует, создайте её:
   ```bash
   ./vendor/bin/sail exec -u root laravel.test sh -c "mkdir -p /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build"
   ```

4. Пересоберите фронтенд:
   ```bash
   ./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && rm -rf public/build && NODE_ENV=production npm run build"
   ```

### Ошибка: Permission denied

**Причина:** Неправильные права доступа к файлам.

**Решение:**

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 755 /var/www/html/public/build"
```

### Ошибка: npm install fails

**Причина:** Проблемы с правами доступа или package-lock.json.

**Решение:**

```bash
./vendor/bin/sail exec -u root laravel.test sh -c "rm -f /var/www/html/package-lock.json && chown -R sail:sail /var/www/html"
./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && npm install"
```

### Проверка статуса контейнеров

```bash
./vendor/bin/sail ps
```

### Просмотр логов контейнера

```bash
./vendor/bin/sail logs laravel.test
```

### Перезапуск контейнеров

```bash
./vendor/bin/sail restart
```

## Проверка успешного деплоя

1. Проверьте наличие манифеста:
   ```bash
   ./vendor/bin/sail exec laravel.test sh -c "test -f /var/www/html/public/build/manifest.json && echo 'OK' || echo 'FAIL'"
   ```

2. Проверьте доступность сайта:
   ```bash
   curl -I http://localhost
   ```

3. Проверьте логи Laravel:
   ```bash
   ./vendor/bin/sail exec laravel.test sh -c "tail -n 50 /var/www/html/storage/logs/laravel.log"
   ```

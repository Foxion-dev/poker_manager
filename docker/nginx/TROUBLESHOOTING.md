# Решение проблем с Nginx на сервере

## Диагностика

Выполните на сервере:

```bash
cd /home/poker_manager

# 1. Проверка статуса всех контейнеров
./vendor/bin/sail ps -a

# 2. Проверка логов nginx
./vendor/bin/sail logs nginx --tail 50

# 3. Проверка логов laravel.test
./vendor/bin/sail logs laravel.test --tail 50

# 4. Проверка доступности laravel.test из nginx
./vendor/bin/sail exec nginx wget -O- http://laravel.test:80 2>&1 | head -20

# 5. Проверка конфигурации nginx
./vendor/bin/sail exec nginx nginx -t

# 6. Проверка сети Docker
docker network inspect poker-manager_sail
```

## Возможные проблемы и решения

### Проблема 1: Контейнер nginx не запускается

**Симптомы:** Контейнер nginx показывает статус "Exited" или не появляется в списке

**Решение:**
```bash
cd /home/poker_manager

# Остановить все контейнеры
./vendor/bin/sail down

# Пересобрать и запустить
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d

# Проверить логи
./vendor/bin/sail logs nginx
```

### Проблема 2: Nginx не может подключиться к laravel.test

**Симптомы:** В логах nginx ошибки типа "upstream connect failed" или "connection refused"

**Решение:**
```bash
cd /home/poker_manager

# Проверить, что laravel.test запущен
./vendor/bin/sail ps | grep laravel.test

# Проверить доступность из nginx
./vendor/bin/sail exec nginx ping -c 3 laravel.test

# Проверить порт 80 в контейнере laravel.test
./vendor/bin/sail exec laravel.test netstat -tlnp | grep 80

# Перезапустить оба контейнера
./vendor/bin/sail restart nginx laravel.test
```

### Проблема 3: Контейнер laravel.test не запускается

**Симптомы:** Контейнер laravel.test показывает статус "Exited" или "Restarting"

**Решение:**
```bash
cd /home/poker_manager

# Проверить логи
./vendor/bin/sail logs laravel.test --tail 100

# Проверить .env файл
cat .env | grep -E "APP_PORT|DB_|REDIS_"

# Пересобрать контейнер
./vendor/bin/sail build --no-cache laravel.test
./vendor/bin/sail up -d laravel.test

# Подождать 30 секунд и проверить статус
sleep 30
./vendor/bin/sail ps
```

### Проблема 4: Конфликт портов

**Симптомы:** Ошибка "port is already allocated" или "bind: address already in use"

**Решение:**
```bash
# Проверить, что использует порт 80
sudo netstat -tlnp | grep :80
sudo lsof -i :80

# Если порт занят другим процессом, остановите его или измените APP_PORT в .env
# В .env установите:
APP_PORT=8080

# Затем перезапустите
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### Проблема 5: Ошибка конфигурации Nginx

**Симптомы:** В логах nginx ошибки синтаксиса или "failed to load configuration"

**Решение:**
```bash
cd /home/poker_manager

# Проверить синтаксис конфигурации
./vendor/bin/sail exec nginx nginx -t

# Если есть ошибки, проверьте файл конфигурации
cat docker/nginx/nginx.conf

# Перезапустить nginx
./vendor/bin/sail restart nginx
```

## Полная переустановка

Если ничего не помогает:

```bash
cd /home/poker_manager

# Остановить все контейнеры
./vendor/bin/sail down -v

# Удалить образы (опционально)
docker rmi sail-8.5/app nginx:alpine

# Пересобрать и запустить
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d

# Подождать запуска
sleep 10

# Проверить статус
./vendor/bin/sail ps

# Проверить логи
./vendor/bin/sail logs nginx
./vendor/bin/sail logs laravel.test
```

## Проверка работы сайта

После исправления проблем проверьте:

```bash
# Проверка доступности через curl
curl -I http://localhost

# Или с IP сервера
curl -I http://YOUR_SERVER_IP

# Проверка из контейнера nginx
./vendor/bin/sail exec nginx wget -O- http://laravel.test:80
```

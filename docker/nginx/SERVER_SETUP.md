# Настройка Nginx на сервере

## Путь к проекту
Проект находится в `/home/poker_manager`

## Вариант 1: Использование только Docker Nginx (рекомендуется)

Docker контейнер Nginx уже настроен и работает на порту 80. Просто запустите:

```bash
cd /home/poker_manager
./vendor/bin/sail up -d
```

Сайт будет доступен на `http://your-server-ip` или через домен, если настроен DNS.

## Вариант 2: Nginx на хосте как прокси к Docker

Если нужно использовать Nginx на хосте для SSL/домена:

1. Установите Nginx на сервере (если еще не установлен):
```bash
sudo apt update
sudo apt install nginx
```

2. Создайте конфигурацию Nginx:
```bash
sudo nano /etc/nginx/sites-available/poker-manager
```

3. Скопируйте содержимое из `docker/nginx/nginx-host.conf.example` и настройте:
   - Замените `your-domain.com` на ваш домен
   - Настройте SSL сертификаты (если используете HTTPS)
   - Убедитесь, что порт в `proxy_pass` совпадает с `APP_PORT` из `.env`

4. Активируйте конфигурацию:
```bash
sudo ln -s /etc/nginx/sites-available/poker-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

5. Убедитесь, что Docker контейнер Nginx слушает на `127.0.0.1:80` (не на `0.0.0.0:80`), чтобы избежать конфликтов с Nginx на хосте.

Для этого в `.env` установите:
```
APP_PORT=127.0.0.1:80
```

Или измените в `compose.yaml`:
```yaml
ports:
    - '127.0.0.1:${APP_PORT:-80}:80'
```

## Проверка работы

После настройки проверьте:
```bash
# Проверка статуса Docker контейнеров
./vendor/bin/sail ps

# Проверка логов Nginx в Docker
./vendor/bin/sail logs nginx

# Проверка логов Nginx на хосте (если используете вариант 2)
sudo tail -f /var/log/nginx/poker-manager-access.log
sudo tail -f /var/log/nginx/poker-manager-error.log
```

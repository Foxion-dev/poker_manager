#!/bin/bash

echo "=== Проверка статуса контейнеров ==="
cd /home/poker_manager
./vendor/bin/sail ps -a

echo ""
echo "=== Проверка логов nginx ==="
./vendor/bin/sail logs nginx --tail 50

echo ""
echo "=== Проверка логов laravel.test ==="
./vendor/bin/sail logs laravel.test --tail 50

echo ""
echo "=== Проверка доступности laravel.test из nginx ==="
./vendor/bin/sail exec nginx wget -O- http://laravel.test:80 2>&1 | head -20

echo ""
echo "=== Проверка конфигурации nginx ==="
./vendor/bin/sail exec nginx nginx -t

echo ""
echo "=== Попытка перезапуска контейнеров ==="
./vendor/bin/sail restart nginx laravel.test

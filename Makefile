COMPOSE_DEV = docker compose -f docker-compose.dev.yml

.PHONY: help start up down migrate migrate-rollback migrate-fresh seed setup-dev deploy deploy-lite setup

help: ## Показать справку по командам
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}'

start: ## [Локально] Запустить проект в dev-режиме (контейнеры + Vite)
	@echo "🚀 Запуск проекта (локальная разработка)..."
	@if ! $(COMPOSE_DEV) ps 2>/dev/null | grep -q "poker-manager-app.*Up"; then \
		echo "📦 Запуск контейнеров..."; \
		$(COMPOSE_DEV) up -d --build; \
		echo "⏳ Ожидание сервисов..."; \
		sleep 5; \
	else \
		echo "✅ Контейнеры запущены"; \
	fi
	@echo "🎨 Vite: http://localhost:5173  |  Приложение: http://localhost:8080"
	@echo ""
	$(COMPOSE_DEV) exec app npm run dev

up: ## [Локально] Запустить контейнеры
	$(COMPOSE_DEV) up -d

down: ## [Локально] Остановить контейнеры
	$(COMPOSE_DEV) down

migrate: ## [Локально] Запустить миграции
	$(COMPOSE_DEV) exec app php artisan migrate

migrate-rollback: ## [Локально] Откатить последнюю миграцию
	$(COMPOSE_DEV) exec app php artisan migrate:rollback --step=1

migrate-fresh: ## [Локально] Пересоздать БД и запустить миграции
	$(COMPOSE_DEV) exec app php artisan migrate:fresh

seed: ## [Локально] Запустить сидеры
	$(COMPOSE_DEV) exec app php artisan db:seed

setup-dev: ## [Локально] Первая настройка: .env, контейнеры, зависимости, миграции
	@echo "Настройка проекта (локальная разработка)..."
	@if [ ! -f .env ]; then cp .env.example .env && echo "Создан .env"; fi
	@test -f .env || (echo "Ошибка: создайте .env"; exit 1)
	$(COMPOSE_DEV) up -d --build
	@sleep 8
	$(COMPOSE_DEV) exec app composer install --no-interaction --prefer-dist
	$(COMPOSE_DEV) exec app php artisan key:generate
	$(COMPOSE_DEV) exec app npm install
	@sleep 5
	$(COMPOSE_DEV) exec app php artisan migrate --seed
	@echo "Готово. Запуск: make start"

deploy: ## [Сервер] Полный деплой: git pull, зависимости, сборка, миграции, кеш
	@echo "🚀 Деплой..."
	@git reset --hard HEAD 2>/dev/null || true
	@git clean -fd 2>/dev/null || true
	@git pull || (echo "⚠️  git pull failed"; exit 1)
	@if ! ./vendor/bin/sail ps 2>/dev/null | grep -q "laravel.test"; then \
		./vendor/bin/sail up -d; \
		sleep 15; \
	fi
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer install --no-interaction --prefer-dist --optimize-autoloader"
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && npm install && NODE_ENV=production npm run build"
	@./vendor/bin/sail exec -u root laravel.test sh -c "mkdir -p /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build"
	@./vendor/bin/sail artisan migrate --force
	@./vendor/bin/sail artisan config:clear
	@./vendor/bin/sail artisan cache:clear
	@./vendor/bin/sail artisan config:cache
	@./vendor/bin/sail artisan route:cache
	@./vendor/bin/sail artisan view:cache
	@echo "✅ Деплой завершён"

deploy-lite: ## [Сервер] Обновить код и фронтенд (без composer, без миграций)
	@echo "🚀 Лёгкий деплой..."
	@git pull || (echo "⚠️  git pull failed"; exit 1)
	@if ! ./vendor/bin/sail ps 2>/dev/null | grep -q "laravel.test"; then \
		./vendor/bin/sail up -d; \
		sleep 10; \
	fi
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && npm install && NODE_ENV=production npm run build"
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html/public/build"
	@echo "✅ Готово"

setup: ## [Сервер] Первая настройка проекта (Sail: контейнеры, ключ, миграции)
	@echo "Настройка проекта на сервере..."
	@if [ ! -f .env ]; then cp .env.example .env && echo "Создан .env"; fi
	@./vendor/bin/sail up -d
	@sleep 10
	@./vendor/bin/sail artisan key:generate
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer install && npm install && NODE_ENV=production npm run build"
	@./vendor/bin/sail artisan migrate --force
	@echo "Готово. Деплой: make deploy"

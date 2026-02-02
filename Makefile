COMPOSE_DEV = docker compose -f docker-compose.dev.yml

.PHONY: help start up down restart build rebuild shell composer npm artisan migrate fresh seed test pint switch-php setup setup-dev deploy deploy-lite

help: ## Показать справку по командам
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

start: ## Запустить проект локально (dev Docker + Vite)
	@echo "🚀 Запуск проекта (локальная разработка)..."
	@if ! $(COMPOSE_DEV) ps 2>/dev/null | grep -q "poker-manager-app.*Up"; then \
		echo "📦 Запуск Docker контейнеров (docker-compose.dev.yml)..."; \
		$(COMPOSE_DEV) up -d --build; \
		echo "⏳ Ожидание запуска сервисов..."; \
		sleep 5; \
	else \
		echo "✅ Контейнеры уже запущены"; \
	fi
	@echo "🎨 Запуск Vite dev сервера..."
	@echo "📝 Приложение: http://localhost:8080"
	@echo "🔧 Vite dev server: http://localhost:5173"
	@echo ""
	$(COMPOSE_DEV) exec app npm run dev

up: ## Запустить контейнеры Docker (локальная разработка)
	$(COMPOSE_DEV) up -d

down: ## Остановить контейнеры Docker (локальная разработка)
	$(COMPOSE_DEV) down

restart: ## Перезапустить контейнеры Docker (локальная разработка)
	$(COMPOSE_DEV) restart

build: ## Пересобрать контейнеры Docker (локальная разработка)
	$(COMPOSE_DEV) build --no-cache

rebuild: ## Остановить, пересобрать и запустить контейнеры (локальная разработка)
	$(COMPOSE_DEV) down
	$(COMPOSE_DEV) build --no-cache
	$(COMPOSE_DEV) up -d

switch-php: ## Переключить версию PHP (использовать: make switch-php VERSION=8.4)
	@if [ -z "$(VERSION)" ]; then \
		echo "Ошибка: Укажите версию PHP. Пример: make switch-php VERSION=8.4"; \
		exit 1; \
	fi
	@echo "Переключение на PHP $(VERSION)..."
	@sed -i '' "s|context: './vendor/laravel/sail/runtimes/[0-9.]*'|context: './vendor/laravel/sail/runtimes/$(VERSION)'|g" compose.yaml
	@sed -i '' "s|image: 'sail-[0-9.]*/app'|image: 'sail-$(VERSION)/app'|g" compose.yaml
	@echo "Версия PHP изменена на $(VERSION)"
	@echo "Теперь выполните: make rebuild"

shell: ## Открыть shell в контейнере приложения (локальная разработка)
	$(COMPOSE_DEV) exec app bash

composer-install: ## Установить PHP зависимости
	$(COMPOSE_DEV) exec app composer install

composer-update: ## Обновить PHP зависимости
	$(COMPOSE_DEV) exec app composer update

composer-require: ## Добавить PHP пакет (использовать: make composer-require PACKAGE=package/name)
	$(COMPOSE_DEV) exec app composer require $(PACKAGE)

npm-install: ## Установить Node.js зависимости
	$(COMPOSE_DEV) exec app npm install

npm-dev: ## Запустить dev сервер Vite
	$(COMPOSE_DEV) exec app npm run dev

npm-build: ## Собрать assets для production
	$(COMPOSE_DEV) exec app npm run build

npm-watch: ## Запустить watch режим для assets
	$(COMPOSE_DEV) exec app npm run dev -- --watch

artisan: ## Выполнить artisan команду (использовать: make artisan CMD="migrate")
	$(COMPOSE_DEV) exec app php artisan $(CMD)

migrate: ## Запустить миграции
	$(COMPOSE_DEV) exec app php artisan migrate

migrate-fresh: ## Пересоздать базу данных и запустить миграции
	$(COMPOSE_DEV) exec app php artisan migrate:fresh

migrate-seed: ## Запустить миграции и сидеры
	$(COMPOSE_DEV) exec app php artisan migrate --seed

seed: ## Запустить сидеры
	$(COMPOSE_DEV) exec app php artisan db:seed

test: ## Запустить тесты
	$(COMPOSE_DEV) exec app php artisan test

pint: ## Запустить Laravel Pint для форматирования кода
	$(COMPOSE_DEV) exec app ./vendor/bin/pint

pint-test: ## Проверить форматирование кода без изменений
	$(COMPOSE_DEV) exec app ./vendor/bin/pint --test

logs: ## Показать логи контейнеров (локальная разработка)
	$(COMPOSE_DEV) logs

logs-follow: ## Показать логи с отслеживанием
	$(COMPOSE_DEV) logs -f

mysql: ## Открыть MySQL CLI
	$(COMPOSE_DEV) exec mysql mysql -ularavel -psecret laravel

redis: ## Открыть Redis CLI
	$(COMPOSE_DEV) exec redis redis-cli

queue-work: ## Запустить worker очереди
	$(COMPOSE_DEV) exec app php artisan queue:work

queue-listen: ## Запустить listener очереди
	$(COMPOSE_DEV) exec app php artisan queue:listen

tinker: ## Открыть Tinker
	$(COMPOSE_DEV) exec app php artisan tinker

clear: ## Очистить кеш приложения
	$(COMPOSE_DEV) exec app php artisan cache:clear
	$(COMPOSE_DEV) exec app php artisan config:clear
	$(COMPOSE_DEV) exec app php artisan route:clear
	$(COMPOSE_DEV) exec app php artisan view:clear

optimize: ## Оптимизировать приложение
	$(COMPOSE_DEV) exec app php artisan config:cache
	$(COMPOSE_DEV) exec app php artisan route:cache
	$(COMPOSE_DEV) exec app php artisan view:cache

fix-permissions: ## Исправить права доступа в контейнере
	@echo "Исправление прав доступа..."
	@$(COMPOSE_DEV) exec -u root app chown -R $$(id -u):$$(id -g) /var/www/html
	@$(COMPOSE_DEV) exec -u root app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
	@echo "Права доступа исправлены!"

setup: ## Первоначальная настройка проекта (сервер, Sail)
	@echo "Настройка проекта..."
	@if [ ! -f .env ]; then cp .env.example .env && echo "Создан .env из .env.example"; fi
	@if [ ! -f ./vendor/bin/sail ]; then \
		echo "Установка зависимостей Composer..."; \
		if command -v composer >/dev/null 2>&1; then \
			composer install --no-interaction --prefer-dist --optimize-autoloader; \
		else \
			php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; \
			php composer-setup.php --quiet; \
			php composer.phar install --no-interaction --prefer-dist --optimize-autoloader; \
			rm -f composer-setup.php composer.phar; \
		fi; \
	fi
	@if [ ! -f ./vendor/bin/sail ]; then \
		echo "Ошибка: vendor/bin/sail не найден. Убедитесь, что зависимости установлены."; \
		exit 1; \
	fi
	./vendor/bin/sail up -d
	@echo "Ожидание запуска контейнеров..."
	@sleep 5
	./vendor/bin/sail artisan key:generate
	./vendor/bin/sail composer install
	@echo "Исправление прав доступа..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache" || true
	./vendor/bin/sail npm install
	@echo "Ожидание запуска MySQL..."
	@sleep 5
	./vendor/bin/sail artisan migrate --seed
	@echo "Проект готов к работе!"

setup-dev: ## Первоначальная настройка для локальной разработки (dev Docker)
	@echo "Настройка проекта (локальная разработка)..."
	@if [ ! -f .env ]; then cp .env.example .env && echo "Создан .env из .env.example"; fi
	@if grep -q '^DB_HOST=' .env 2>/dev/null; then sed -i.bak 's/^DB_HOST=.*/DB_HOST=mysql/' .env 2>/dev/null; else echo "DB_HOST=mysql" >> .env; fi
	@if ! grep -q '^REDIS_HOST=' .env 2>/dev/null; then echo "REDIS_HOST=redis" >> .env; fi
	@echo "Запуск контейнеров..."
	$(COMPOSE_DEV) up -d --build
	@echo "Ожидание запуска сервисов..."
	@sleep 8
	$(COMPOSE_DEV) exec app composer install --no-interaction --prefer-dist
	$(COMPOSE_DEV) exec app php artisan key:generate
	$(COMPOSE_DEV) exec app npm install
	@echo "Ожидание готовности MySQL..."
	@sleep 5
	$(COMPOSE_DEV) exec app php artisan migrate --seed
	@echo "Проект готов к работе! Запустите: make start"

deploy: ## Деплой проекта: подтянуть код из git и пересобрать
	@echo "🚀 Начало деплоя..."
	@echo "🔄 Отмена локальных изменений..."
	@git reset --hard HEAD || echo "⚠️  Не удалось сбросить изменения"
	@git clean -fd || echo "⚠️  Не удалось очистить неотслеживаемые файлы"
	@echo "✅ Локальные изменения отменены"
	@echo ""
	@echo "📥 Подтягивание изменений из git..."
	@git pull || echo "⚠️  Ошибка при git pull. Продолжаем деплой..."
	@echo "✅ Код обновлен"
	@echo ""
	@echo "📦 Проверка контейнеров..."
	@if ! ./vendor/bin/sail ps | grep -q "laravel.test"; then \
		echo "⚠️  Контейнеры не запущены. Запускаем..."; \
		./vendor/bin/sail up -d; \
		echo "⏳ Ожидание готовности сервисов (MySQL start_period 30s)..."; \
		for i in 1 2 3 4 5 6 7 8 9 10; do \
			if ./vendor/bin/sail ps | grep -q "laravel.test.*healthy"; then \
				echo "✅ Контейнеры готовы (попытка $$i)"; \
				break; \
			fi; \
			echo "⏳ Попытка $$i/10..."; \
			sleep 5; \
			if [ $$i -eq 10 ]; then \
				echo "❌ Контейнер laravel.test не запустился. Логи MySQL:"; \
				./vendor/bin/sail logs mysql --tail=30 2>&1 || true; \
				exit 1; \
			fi; \
		done; \
	fi
	@if ! ./vendor/bin/sail ps | grep -q "laravel.test"; then \
		echo "❌ Контейнер laravel.test не запущен. Проверьте логи: make logs"; \
		exit 1; \
	fi
	@echo "✅ Контейнеры запущены"
	@echo ""
	@echo "📦 Установка PHP зависимостей..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer install --no-interaction --prefer-dist --no-scripts --quiet 2>&1" || (echo "⚠️  Composer install завершился с ошибкой" && exit 1)
	@echo "✅ PHP зависимости установлены"
	@echo ""
	@echo "🔧 Оптимизация autoload и обнаружение пакетов..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer dump-autoload --optimize --no-interaction --quiet 2>&1" || true
	@./vendor/bin/sail artisan package:discover --ansi --quiet || true
	@echo "✅ Autoload оптимизирован, пакеты обнаружены"
	@echo ""
	@echo "🔧 Исправление прав доступа перед установкой npm..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && rm -f /var/www/html/package-lock.json" || true
	@echo "✅ Права доступа исправлены"
	@echo "📦 Установка Node.js зависимостей..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && whoami && npm install"
	@echo "✅ Node.js зависимости установлены"
	@echo ""
	@echo "📁 Создание директории для сборки..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "mkdir -p /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build && chmod -R 755 /var/www/html/public/build" || true
	@echo "✅ Директория создана"
	@echo ""
	@echo "🎨 Сборка assets для production..."
	@echo "⏳ Это может занять несколько минут, пожалуйста подождите..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && NODE_ENV=production npm run build 2>&1" || (echo "❌ Ошибка при сборке assets!" && exit 1)
	@echo "✅ Assets собраны"
	@echo ""
	@echo "🔧 Исправление прав доступа и проверка манифеста..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html/public/build && chmod -R 755 /var/www/html/public/build && if [ -f /var/www/html/public/build/.vite/manifest.json ] && [ ! -f /var/www/html/public/build/manifest.json ]; then cp /var/www/html/public/build/.vite/manifest.json /var/www/html/public/build/manifest.json && chown sail:sail /var/www/html/public/build/manifest.json; fi" || true
	@./vendor/bin/sail exec laravel.test sh -c "if [ -f /var/www/html/public/build/manifest.json ]; then echo '✅ Манифест найден'; else echo '❌ Манифест не найден!'; ls -la /var/www/html/public/build/ 2>&1; exit 1; fi" || (echo "⚠️  Проблема с проверкой манифеста" && exit 1)
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache" || true
	@echo "✅ Права доступа исправлены"
	@echo ""
	@echo "🗄️  Проверка и запуск MySQL..."
	@MYSQL_CONTAINER=$$(docker ps -q -f name=mysql 2>/dev/null || echo ""); \
	if [ -z "$$MYSQL_CONTAINER" ]; then \
		echo "⚠️  MySQL контейнер не запущен. Запускаем..."; \
		./vendor/bin/sail up -d mysql 2>&1 || echo "⚠️  Ошибка при запуске MySQL"; \
		echo "⏳ Ожидание запуска MySQL (10 секунд)..."; \
		sleep 10; \
	fi
	@echo "⏳ Проверка готовности MySQL..."
	@for i in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15; do \
		MYSQL_CONTAINER=$$(docker ps -q -f name=mysql 2>/dev/null || echo ""); \
		if [ -n "$$MYSQL_CONTAINER" ]; then \
			if docker exec $$MYSQL_CONTAINER mysqladmin ping -h localhost --silent 2>/dev/null || docker exec $$MYSQL_CONTAINER mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then \
				echo "✅ MySQL готов (попытка $$i)"; \
				break; \
			fi; \
		fi; \
		if [ $$i -le 5 ] || [ $$(( $$i % 3 )) -eq 0 ]; then \
			echo "⏳ Попытка $$i/15..."; \
		fi; \
		sleep 2; \
		if [ $$i -eq 15 ]; then \
			echo "❌ MySQL не запустился за 30 секунд"; \
			echo "Проверка статуса всех контейнеров:"; \
			./vendor/bin/sail ps -a 2>&1 || docker ps -a 2>&1 || true; \
			echo ""; \
			echo "Попытка перезапуска MySQL..."; \
			./vendor/bin/sail restart mysql 2>&1 || ./vendor/bin/sail up -d mysql 2>&1 || true; \
			sleep 5; \
			echo "Повторная проверка готовности MySQL (еще 10 секунд)..."; \
			for j in 1 2 3 4 5; do \
				MYSQL_CONTAINER=$$(docker ps -q -f name=mysql 2>/dev/null || echo ""); \
				if [ -n "$$MYSQL_CONTAINER" ]; then \
					if docker exec $$MYSQL_CONTAINER mysqladmin ping -h localhost --silent 2>/dev/null || docker exec $$MYSQL_CONTAINER mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then \
						echo "✅ MySQL готов после перезапуска"; \
						break; \
					fi; \
				fi; \
				sleep 2; \
			done; \
			MYSQL_CONTAINER=$$(docker ps -q -f name=mysql 2>/dev/null || echo ""); \
			if [ -z "$$MYSQL_CONTAINER" ] || (! docker exec $$MYSQL_CONTAINER mysqladmin ping -h localhost --silent 2>/dev/null && ! docker exec $$MYSQL_CONTAINER mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null); then \
				echo "❌ MySQL все еще не готов. Проверьте логи: ./vendor/bin/sail logs mysql"; \
				exit 1; \
			fi; \
		fi; \
	done
	@echo ""
	@echo "🔧 Проверка .env для Docker (DB_HOST=mysql)..."
	@if [ -f .env ]; then if grep -q '^DB_HOST=' .env; then sed -i.bak 's/^DB_HOST=.*/DB_HOST=mysql/' .env; else echo 'DB_HOST=mysql' >> .env; fi; fi
	@echo "🗄️  Запуск миграций..."
	@./vendor/bin/sail artisan config:clear
	@./vendor/bin/sail artisan migrate --force || (echo "⚠️  Ошибка при миграциях. Проверьте логи." && echo "Статус контейнеров:" && ./vendor/bin/sail ps && echo "Логи MySQL:" && ./vendor/bin/sail logs mysql --tail=20 && exit 1)
	@echo "✅ Миграции выполнены успешно"
	@echo ""
	@echo "🧹 Очистка кеша..."
	@./vendor/bin/sail artisan cache:clear || true
	@./vendor/bin/sail artisan config:clear || true
	@./vendor/bin/sail artisan route:clear || true
	@./vendor/bin/sail artisan view:clear || true
	@echo "✅ Кеш очищен"
	@echo ""
	@echo "⚡ Оптимизация приложения..."
	@./vendor/bin/sail artisan config:cache || true
	@./vendor/bin/sail artisan route:cache || true
	@./vendor/bin/sail artisan view:cache || true
	@echo "✅ Приложение оптимизировано"
	@echo ""
	@echo "🎉 Деплой завершен успешно!"

deploy-lite: ## Обновить код и фронтенд без полного деплоя
	@echo "🚀 Начало легкого деплоя..."
	@echo "📥 Подтягивание изменений из git..."
	@git pull || echo "⚠️  Ошибка при git pull. Продолжаем деплой..."
	@echo "✅ Код обновлен"
	@echo ""
	@echo "📦 Проверка контейнеров..."
	@if ! ./vendor/bin/sail ps | grep -q "laravel.test"; then \
		echo "⚠️  Контейнеры не запущены. Запускаем..."; \
		./vendor/bin/sail up -d; \
		echo "⏳ Ожидание готовности сервисов (несколько секунд)..."; \
		sleep 10; \
	fi
	@echo "✅ Контейнеры запущены"
	@echo ""
	@echo "📦 Установка Node.js зависимостей..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && npm install"
	@echo "✅ Node.js зависимости установлены"
	@echo ""
	@echo "📁 Создание директории для сборки..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "mkdir -p /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build && chmod -R 755 /var/www/html/public/build" || true
	@echo "✅ Директория создана"
	@echo ""
	@echo "🎨 Сборка assets для production..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && NODE_ENV=production npm run build 2>&1" || (echo "❌ Ошибка при сборке assets!" && exit 1)
	@echo "✅ Assets собраны"
	@echo ""
	@echo "🎉 Легкий деплой завершен успешно!"

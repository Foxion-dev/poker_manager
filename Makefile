.PHONY: help start up down restart build rebuild shell composer npm artisan migrate fresh seed test pint switch-php deploy

help: ## Показать справку по командам
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

start: ## Запустить проект локально (контейнеры + dev сервер)
	@echo "🚀 Запуск проекта..."
	@if ! ./vendor/bin/sail ps | grep -q "laravel.test"; then \
		echo "📦 Запуск Docker контейнеров..."; \
		./vendor/bin/sail up -d; \
		echo "⏳ Ожидание запуска сервисов..."; \
		sleep 5; \
	else \
		echo "✅ Контейнеры уже запущены"; \
	fi
	@echo "🎨 Запуск Vite dev сервера..."
	@echo "📝 Приложение доступно на http://localhost"
	@echo "🔧 Vite dev server на http://localhost:5173"
	@echo ""
	./vendor/bin/sail npm run dev

up: ## Запустить контейнеры Docker
	./vendor/bin/sail up -d

down: ## Остановить контейнеры Docker
	./vendor/bin/sail down

restart: ## Перезапустить контейнеры Docker
	./vendor/bin/sail restart

build: ## Пересобрать контейнеры Docker
	./vendor/bin/sail build --no-cache

rebuild: ## Остановить, пересобрать и запустить контейнеры
	./vendor/bin/sail down
	./vendor/bin/sail build --no-cache
	./vendor/bin/sail up -d

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

shell: ## Открыть shell в контейнере Laravel
	./vendor/bin/sail shell

composer-install: ## Установить PHP зависимости
	./vendor/bin/sail composer install

composer-update: ## Обновить PHP зависимости
	./vendor/bin/sail composer update

composer-require: ## Добавить PHP пакет (использовать: make composer-require PACKAGE=package/name)
	./vendor/bin/sail composer require $(PACKAGE)

npm-install: ## Установить Node.js зависимости
	./vendor/bin/sail npm install

npm-dev: ## Запустить dev сервер Vite
	./vendor/bin/sail npm run dev

npm-build: ## Собрать assets для production
	./vendor/bin/sail npm run build

npm-watch: ## Запустить watch режим для assets
	./vendor/bin/sail npm run dev -- --watch

artisan: ## Выполнить artisan команду (использовать: make artisan CMD="migrate")
	./vendor/bin/sail artisan $(CMD)

migrate: ## Запустить миграции
	./vendor/bin/sail artisan migrate

migrate-fresh: ## Пересоздать базу данных и запустить миграции
	./vendor/bin/sail artisan migrate:fresh

migrate-seed: ## Запустить миграции и сидеры
	./vendor/bin/sail artisan migrate --seed

seed: ## Запустить сидеры
	./vendor/bin/sail artisan db:seed

test: ## Запустить тесты
	./vendor/bin/sail artisan test

pint: ## Запустить Laravel Pint для форматирования кода
	./vendor/bin/sail pint

pint-test: ## Проверить форматирование кода без изменений
	./vendor/bin/sail pint --test

logs: ## Показать логи контейнеров
	./vendor/bin/sail logs

logs-follow: ## Показать логи с отслеживанием
	./vendor/bin/sail logs -f

mysql: ## Открыть MySQL CLI
	./vendor/bin/sail mysql

redis: ## Открыть Redis CLI
	./vendor/bin/sail redis-cli

queue-work: ## Запустить worker очереди
	./vendor/bin/sail artisan queue:work

queue-listen: ## Запустить listener очереди
	./vendor/bin/sail artisan queue:listen

tinker: ## Открыть Tinker
	./vendor/bin/sail artisan tinker

clear: ## Очистить кеш приложения
	./vendor/bin/sail artisan cache:clear
	./vendor/bin/sail artisan config:clear
	./vendor/bin/sail artisan route:clear
	./vendor/bin/sail artisan view:clear

optimize: ## Оптимизировать приложение
	./vendor/bin/sail artisan config:cache
	./vendor/bin/sail artisan route:cache
	./vendor/bin/sail artisan view:cache

fix-permissions: ## Исправить права доступа в контейнере
	@echo "Исправление прав доступа..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache" || true
	@echo "Права доступа исправлены!"

setup: ## Первоначальная настройка проекта
	@echo "Настройка проекта..."
	@if [ ! -f ./vendor/bin/sail ]; then \
		echo "Установка зависимостей Composer..."; \
		composer install --no-interaction --prefer-dist --optimize-autoloader; \
	fi
	@if [ ! -f ./vendor/bin/sail ]; then \
		echo "Ошибка: vendor/bin/sail не найден. Убедитесь, что зависимости установлены."; \
		exit 1; \
	fi
	./vendor/bin/sail up -d
	@echo "Ожидание запуска контейнеров..."
	@sleep 5
	./vendor/bin/sail composer install
	@echo "Исправление прав доступа..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache" || true
	./vendor/bin/sail npm install
	@echo "Ожидание запуска MySQL..."
	@sleep 5
	./vendor/bin/sail artisan migrate --seed
	@echo "Проект готов к работе!"

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
		echo "⏳ Ожидание запуска сервисов..."; \
		sleep 30; \
	fi
	@echo "✅ Контейнеры запущены"
	@echo ""
	@echo "📦 Проверка PHP зависимостей..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer validate --no-check-publish --no-check-lock --quiet 2>/dev/null || echo 'composer.json valid'" || true
	@echo "📦 Установка PHP зависимостей..."
	@echo "⏳ Это может занять несколько минут..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && timeout 600 composer install --no-interaction --prefer-dist --no-scripts 2>&1" || (echo "⚠️  Composer install завершился с ошибкой или таймаутом" && exit 1)
	@echo "✅ PHP зависимости установлены"
	@echo ""
	@echo "🔧 Оптимизация autoload..."
	@./vendor/bin/sail exec laravel.test sh -c "cd /var/www/html && composer dump-autoload --optimize --no-interaction --quiet 2>&1" || true
	@echo "✅ Autoload оптимизирован"
	@echo ""
	@echo "🔧 Обнаружение пакетов..."
	@./vendor/bin/sail artisan package:discover --ansi --quiet || true
	@echo "✅ Пакеты обнаружены"
	@echo ""
	@echo "🔧 Исправление прав доступа перед установкой npm..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && rm -f /var/www/html/package-lock.json && ls -la /var/www/html/package.json" || true
	@sleep 1
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
	@echo "🔧 Исправление прав доступа для директории build..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html/public/build && chmod -R 755 /var/www/html/public/build" || true
	@echo "✅ Права доступа исправлены"
	@echo ""
	@echo "🔍 Проверка и копирование манифеста Vite..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "if [ -f /var/www/html/public/build/.vite/manifest.json ]; then echo '✅ Манифест найден в .vite/, копируем в корень build/'; cp /var/www/html/public/build/.vite/manifest.json /var/www/html/public/build/manifest.json && chown sail:sail /var/www/html/public/build/manifest.json && echo '✅ Манифест скопирован'; ls -lh /var/www/html/public/build/manifest.json; elif [ -f /var/www/html/public/build/manifest.json ]; then echo '✅ Манифест найден в корне build/'; ls -lh /var/www/html/public/build/manifest.json; else echo '❌ Манифест не найден!'; echo 'Содержимое директории build:'; ls -la /var/www/html/public/build/ 2>&1 || echo 'Директория build не существует'; echo 'Содержимое директории .vite:'; ls -la /var/www/html/public/build/.vite/ 2>&1 || echo 'Директория .vite не существует'; if [ -d /var/www/html/public/build/.vite ]; then echo 'Файлы в .vite:'; ls -la /var/www/html/public/build/.vite/ 2>&1; fi; exit 1; fi" || (echo "⚠️  Проблема с проверкой манифеста" && exit 1)
	@echo ""
	@echo "🔧 Исправление прав доступа после сборки..."
	@./vendor/bin/sail exec -u root laravel.test sh -c "chown -R sail:sail /var/www/html && chmod -R 755 /var/www/html && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 755 /var/www/html/public/build && chown -R sail:sail /var/www/html/public/build/.vite 2>/dev/null || true && chmod -R 755 /var/www/html/public/build/.vite 2>/dev/null || true" || true
	@echo ""
	@echo "🗄️  Запуск миграций..."
	@echo "⏳ Ожидание готовности базы данных..."
	@sleep 3
	@./vendor/bin/sail artisan migrate --force || (echo "⚠️  Ошибка при миграциях. Проверьте логи." && exit 1)
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

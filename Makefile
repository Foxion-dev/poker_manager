.PHONY: help up down restart build shell composer npm artisan migrate fresh seed test pint

help: ## Показать справку по командам
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Запустить контейнеры Docker
	./vendor/bin/sail up -d

down: ## Остановить контейнеры Docker
	./vendor/bin/sail down

restart: ## Перезапустить контейнеры Docker
	./vendor/bin/sail restart

build: ## Пересобрать контейнеры Docker
	./vendor/bin/sail build --no-cache

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

setup: ## Первоначальная настройка проекта
	@echo "Настройка проекта..."
	./vendor/bin/sail up -d
	./vendor/bin/sail composer install
	./vendor/bin/sail npm install
	@echo "Ожидание запуска MySQL..."
	@sleep 5
	./vendor/bin/sail artisan migrate --seed
	@echo "Проект готов к работе!"

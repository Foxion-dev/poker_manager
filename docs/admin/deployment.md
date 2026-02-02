# Деплой

Полная пошаговая инструкция по выкладке на сервер: **[DEPLOY.md](../DEPLOY.md)**.

## Кратко

- **Автодеплой:** при пуше в `main` выполняется GitHub Actions workflow: SSH на сервер и `make deploy` (или `make deploy-lite`) в каталоге проекта.
- **Ручной деплой на сервере:**
  ```bash
  cd /home/poker_manager
  make deploy
  ```
- **Лёгкий деплой** (без миграций и `composer install`):
  ```bash
  make deploy-lite
  ```

## Секреты GitHub Actions

В настройках репозитория (Secrets and variables → Actions) задаются:

| Секрет | Описание |
|--------|----------|
| `DEPLOY_HOST` | IP или hostname сервера |
| `DEPLOY_USER` | SSH-пользователь |
| `DEPLOY_SSH_KEY` | Приватный SSH-ключ |
| `DEPLOY_SSH_PASSPHRASE` | (опционально) Пароль от ключа |
| `DEPLOY_PORT` | (опционально) Порт SSH |

На сервере в `~/.ssh/authorized_keys` должен быть добавлен соответствующий публичный ключ.

## После деплоя

- Проверить доступность сайта и HTTPS.
- При использовании Telegram-бота: проверить `APP_URL`, сохранить настройки бота в админке (см. [Telegram](telegram.md)).
- При необходимости выполнить миграции вручную: `php artisan migrate --force` (или через Sail/контейнер).

# Release 0 — Foundation

> **Исторический документ Release 0.** Этот файл фиксирует scope и контракты Foundation на момент исходного baseline `974b360afc459e3760486835fcf71ec9a614426c`; он не описывает текущее состояние всего проекта. Актуальные модули, версии и текущая RBAC-матрица находятся в [`PROJECT_STATE.md`](PROJECT_STATE.md). Permissions последующих релизов не следует добавлять в историческую таблицу ниже.

## Назначение

На этапе Release 0 Foundation предоставлял базовый Laravel-проект, административную панель, пользователей, роли, permissions, безопасное создание первого администратора и расширяемое хранилище глобальных настроек. Функциональность последующих релизов на тот момент намеренно не была реализована.

Laravel размещён в корне репозитория. Документация находится в `docs/`; отдельная вложенная папка приложения не используется.

## Стек

- PHP 8.3+;
- Laravel 12.65.0;
- Filament 4.12.6, панель `/admin`;
- Spatie Laravel Permission 8.3.0;
- MySQL 8+ для целевого окружения;
- SQLite in-memory для автоматических тестов;
- Blade, Vite 7.3.6, Tailwind CSS 4.3.3;
- Laravel Pint 1.30.5.

Filament Shield не установлен: системные ресурсы и Policies реализованы напрямую на Filament + Spatie, поэтому дополнительный слой не требуется.

## Конфигурация

Скопируйте `.env.example` в `.env`, создайте уникальный `APP_KEY` и задайте реквизиты MySQL. Значения по умолчанию:

- `APP_TIMEZONE=Europe/Kaliningrad`;
- `APP_LOCALE=ru`;
- `APP_FALLBACK_LOCALE=ru`;
- `APP_FAKER_LOCALE=ru_RU`;
- `DB_CONNECTION=mysql`.

Секреты и production-реквизиты не должны попадать в git.

## Миграции и seed

```bash
php artisan migrate --seed
```

`DatabaseSeeder` вызывает только `RolesAndPermissionsSeeder`. Пользователь с известным паролем не создаётся. Регистрация ролей и permissions идемпотентна: повторный запуск синхронизирует матрицу и не создаёт дубликаты.

## Роли

| Роль | Разрешения в scope Release 0 |
|---|---|
| `super-admin` | Все permissions Release 0: `admin.access`, `users.*`, `roles.*`, `content.*`, `settings.view`, `settings.manage` |
| `content-manager` | `admin.access`, `content.*`, `settings.view` |
| `sales-manager` | `admin.access` |
| `viewer` | `admin.access`, `content.view`, `settings.view` |

Baseline-коммит объединяет состояние проекта через Release 2 и уже содержит зарезервированные enum/matrix entries для будущих Leads, Genplan и Plots. Эти permissions не являются функциональностью Release 0 и намеренно исключены из исторической таблицы. Текущий полный реестр хранится в `App\Domain\Users\Enums\PermissionName`, текущая матрица — в `App\Domain\Users\RolePermissionRegistrar`; их актуальный контракт описан в `PROJECT_STATE.md`.

## Авторизация

`User::canAccessPanel()` требует одновременно:

- панель `admin`;
- активного пользователя;
- permission `admin.access`.

`Gate::before` предоставляет роли `super-admin` все гранулярные permissions с точечной нотацией. Контекстные действия моделей (`update`, `delete`) всё равно проходят Policies, поэтому bypass не отключает защиту собственной учётной записи и системной роли.

Ресурсы Users и Roles используют `UserPolicy` и `RolePolicy`. В UI запрещены массовые удаления. Нельзя удалить собственную учётную запись, удалить последнего активного super-admin, изменить или удалить роль `super-admin`, а в собственной форме super-admin нельзя отключить активность или снять роли.

## Создание super-admin

```bash
php artisan app:create-super-admin
```

Команда валидирует имя и уникальный email, дважды скрыто запрашивает пароль длиной не менее 12 символов, активирует пользователя, отмечает email подтверждённым и назначает `super-admin`. Пароль нельзя передать option-параметром.

## Settings Foundation

Таблица `settings` хранит уникальный ключ, JSON-значение, тип, группу и признак публичности. `App\Domain\Settings\SettingsRepository` предоставляет `get`, `set` и `forget`, автоматически фиксирует тип и инвалидирует cache после записи.

Полноценный UI контактов и SEO отсутствует до следующих релизов. Новые настройки должны добавляться через существующий механизм, без параллельной settings-системы.

## Frontend и storage

Tailwind CSS подключён к публичному bundle через Vite. Filament публикует и загружает собственные admin-assets; публичный frontend не зависит от стилей панели. После установки выполните:

```bash
php artisan storage:link
npm ci
npm run build
```

## Проверки

```bash
composer validate --strict
php artisan test
php artisan migrate:fresh --seed
vendor/bin/pint --test
npm run build
```

Тесты проверяют доступ `/admin`, матрицу ролей, отсутствие write-прав у viewer, идемпотентность seed, protected super-admin flow, команду создания администратора, системные Filament routes и Settings Repository.

## Ограничения Release 0

На момент Release 0 не были реализованы публичные страницы, контентные ресурсы, блог, заявки, контакты/SEO UI, генплан, участки, карты и внешние интеграции. Это историческое ограничение Release 0, а не описание текущего проекта. Локальная проверка Foundation была выполнена на SQLite, поскольку MySQL-сервис в проверочном окружении отсутствовал; актуальный статус MySQL-проверки указан в `PROJECT_STATE.md`.

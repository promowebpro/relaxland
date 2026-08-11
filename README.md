# RelaxLand

Публичный сайт и административная панель RelaxLand на Laravel. Главный источник требований — [RELAXLAND_SPEC.md](RELAXLAND_SPEC.md), фактическое состояние разработки — [docs/PROJECT_STATE.md](docs/PROJECT_STATE.md).

Реализованы Release 0 Foundation, Release 1 Shared Frontend + Simple Pages и Release 2 Blog. Доступны Contacts, юридические документы, success/thanks, кастомная 404, управляемый Blog, Settings UI и Content CRUD. Главная, заявки и генплан ещё не разрабатывались.

## Требования

- PHP 8.3+ с расширениями `curl`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `zip`;
- Composer 2;
- MySQL 8+;
- Node.js и npm;
- доступный для записи каталог `storage/`.

Зафиксированный стек: Laravel 12, Filament 4, Spatie Laravel Permission 8, Blade, Vite и Tailwind CSS 4.

## Установка

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Укажите собственные реквизиты MySQL в `.env`. Не коммитьте `.env` и реальные секреты.

```bash
php artisan migrate --seed
php artisan storage:link
npm ci
npm run build
```

Для локальной разработки:

```bash
php artisan serve
npm run dev
```

## Первый администратор

Production-администратор не создаётся сидером. Запустите интерактивную команду:

```bash
php artisan app:create-super-admin
```

Имя и email также можно передать безопасными параметрами; пароль всегда запрашивается скрыто и не попадает в историю shell:

```bash
php artisan app:create-super-admin --name="Administrator" --email="admin@example.com"
```

После создания вход доступен по адресу `/admin`.

Разделы Release 1 в Filament:

- `/admin/site-settings` — публичные контакты, соцсети, маршруты, презентация и footer;
- `/admin/legal-documents` — HTML/PDF юридические документы и публикация.
- `/admin/blog-categories` — категории блога;
- `/admin/blog-posts` — статьи, контент-блоки, media, публикация и SEO.

Публичные маршруты:

- `/contacts`;
- `/privacy` и `/privacy/{slug}`;
- `/thanks`;
- `/blog` и `/blog/{slug}`;
- `/` временно показывает Contacts до реализации главной в Release 3.

## Проверки

```bash
composer validate --strict
php artisan test
php artisan migrate:fresh --seed
vendor/bin/pint --test
npm run build
```

`migrate:fresh` удаляет данные выбранной БД и предназначен только для локальной или тестовой среды.

Подробности Foundation, матрица ролей и архитектурные решения описаны в [docs/FOUNDATION.md](docs/FOUNDATION.md).

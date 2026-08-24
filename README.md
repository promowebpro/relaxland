# RelaxLand

Публичный SSR-сайт и административная панель RelaxLand на Laravel. Источник требований — [RELAXLAND_SPEC.md](RELAXLAND_SPEC.md), актуальное состояние — [docs/PROJECT_STATE.md](docs/PROJECT_STATE.md), production launch gates — [docs/LAUNCH_CHECKLIST.md](docs/LAUNCH_CHECKLIST.md).

Реализованы Foundation, shared frontend, Blog, Home, Leads, Genplan 2D/3D interactions, public Plot selection, Surroundings SSR fallback и Release 9 SEO/production-readiness foundation. Интерактивный map provider остаётся `DEFERRED`; отсутствие ключа или SDK не ломает публичный список окружения.

## Требования

- PHP 8.3+ с `curl`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `zip`;
- Composer 2 в build environment;
- MySQL 8+ для production;
- актуальные Node.js/npm для Vite 7 в build environment;
- writable `storage/` и `bootstrap/cache/`.

Стек: Laravel 12, Filament 4, Spatie Laravel Permission 8, Blade, Vite и Tailwind CSS 4.

## Установка для разработки

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Для локального HTTP измените safe production-oriented пример: `APP_ENV=local`, `APP_URL=http://127.0.0.1:8000`, `SEO_PUBLIC_URL` на тот же origin, `SESSION_SECURE_COOKIE=false`. Индексацию оставьте выключенной. Укажите собственную development DB.

```bash
php artisan migrate --seed
php artisan storage:link
npm ci
npm run build
php artisan serve
```

## Первый администратор

Production-администратор не создаётся сидером:

```bash
php artisan app:create-super-admin --name="Administrator" --email="admin@example.com"
```

Пароль всегда запрашивается скрыто и не попадает в history. Вход: `/admin`.

Основные разделы Filament:

- `/admin/site-settings` — контакты, соцсети, документы/footer и глобальные SEO fallback;
- `/admin/home`, `/admin/blog-categories`, `/admin/blog-posts`, `/admin/legal-documents`, `/admin/stories`;
- `/admin/leads`;
- `/admin/genplans`, `/admin/quarters`, `/admin/plots`, `/admin/infrastructure-points`, `/admin/surrounding-places`;
- `/admin/users`, `/admin/roles`.

## Публичные endpoints

- `/`, `/about`, `/contacts`, `/thanks`;
- `/privacy`, `/privacy/{slug}`;
- `/blog`, `/blog/{slug}`;
- `/genplan` и controlled deep-link query states;
- read-only `/api/genplan/*`;
- `POST /leads`;
- `/robots.txt`, `/sitemap.xml`, `/up`.

Полный SEO/query/index contract: [docs/SEO_CONTRACT.md](docs/SEO_CONTRACT.md).

## Проверки

```bash
composer validate --strict
composer audit --locked
php artisan test
php artisan migrate:fresh --seed
vendor/bin/pint --test
npm audit --audit-level=moderate
npm run test:js
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`migrate:fresh` удаляет данные выбранной БД и разрешён только для явно созданной local/verification DB. Production использует только reviewed `php artisan migrate --force` после backup.

Безопасный configuration audit без вывода секретов:

```bash
php artisan app:production-check
```

Production runbooks: [deployment/PRODUCTION.md](deployment/PRODUCTION.md) и [deployment/BACKUP_RESTORE.md](deployment/BACKUP_RESTORE.md). Release 9 не выполняет production deployment, DNS/SSL, mail/analytics или map-provider integration.

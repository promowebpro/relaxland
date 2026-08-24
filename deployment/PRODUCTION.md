# RelaxLand Production Deployment Runbook

Статус: **RUNBOOK READY / PRODUCTION INFRASTRUCTURE PENDING**. Документ не является выполненным deployment.

## Current staging audit (2026-08-24)

- shared hosting Reg.ru, приложение: `/var/www/u3465154/data/apps/relaxland.evoline.digital`;
- PHP CLI: `php83`, версия 8.3.31;
- current staging revision: `ff4fecd` на момент аудита;
- `public/storage` link присутствует;
- APP_ENV production, APP_DEBUG false, APP_URL HTTPS;
- cache/session/queue configured as database;
- фактически DB default — SQLite, поэтому MySQL 8 verification и production migration остаются pending;
- mail — `log`; реальная доставка pending;
- Composer CLI отсутствует;
- server npm 6.14.11 / Node toolchain не пригоден для Vite 7 production build;
- scheduled tasks отсутствуют; persistent queue worker не подтверждён;
- map key отсутствует, provider deferred, SSR fallback работает;
- Release 9 indexing flag ещё не развернут.

Следствие: production release artifact должен собираться в CI/доверенной build-среде с PHP 8.3-compatible Composer и актуальным Node, включать `vendor/` и `public/build/`, либо hosting должен предоставить отдельный согласованный Composer runtime. Сборка на текущем shared hosting не считается доступной.

## 1. Prerequisites

- назначить ответственного за deployment и rollback;
- подтвердить production domain, DNS, SSL и единственный canonical host;
- предоставить MySQL 8+ verification и production databases с отдельными credentials;
- проверить PHP 8.3+ extensions из README;
- подготовить release artifact из reviewed commit;
- подтвердить writable `storage/` и `bootstrap/cache/`;
- подтвердить backup location, encryption/access и restore owner;
- оставить `SEO_INDEXING_ENABLED=false` до post-deploy smoke tests;
- не подключать map provider, analytics, mail/SMS или другие внешние services без отдельного решения.

## 2. Environment contract

Заполнить production `.env` вручную из `.env.example`; файл не копируется в release artifact и не выводится в logs. Обязательные группы:

- APP_ENV=production, APP_DEBUG=false, unique APP_KEY;
- APP_URL и SEO_PUBLIC_URL — один фактический HTTPS origin;
- SEO_INDEXING_ENABLED=false на время rollout;
- MySQL 8 credentials;
- database cache/session/queue drivers;
- SESSION_SECURE_COOKIE=true, SESSION_HTTP_ONLY=true, SESSION_ENCRYPT=true;
- LOG_STACK=daily, LOG_LEVEL=info, согласованный retention;
- public filesystem и storage link;
- TRUSTED_PROXIES — только проверенные proxy IP/CIDR, пусто при прямом соединении;
- HSTS_ENABLED=false до подтверждения стабильного HTTPS на конечном host;
- SURROUNDINGS_MAP_ENABLED=false и пустой YANDEX_MAPS_API_KEY при текущем deferred status;
- mail остаётся `log` до отдельного подключения provider; это launch pending.

Запустить безопасную проверку без вывода значений:

```bash
php83 artisan app:production-check
```

`BLOCKER` запрещает rollout. `PENDING INFRASTRUCTURE` переносится в launch checklist и требует ручного решения владельца.

## 3. Pre-deploy snapshot

1. Зафиксировать текущий commit и release artifact ID.
2. Проверить абсолютный application path командой `pwd`; не продолжать, если он не совпадает с утверждённым.
3. Выполнить database и uploaded media backup по `deployment/BACKUP_RESTORE.md`.
4. Проверить читаемость backup metadata и наличие ответственного за restore.
5. Не использовать `migrate:fresh`, `db:wipe`, `git reset --hard` или удаление storage.

## 4. Artifact deployment

Preferred shared-hosting flow:

1. CI/build host: `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`.
2. CI/build host: `npm ci && npm run build`.
3. Проверить отсутствие `.env`, verification DB, logs, screenshots и browser profiles в artifact.
4. Передать versioned artifact в отдельную release directory.
5. Сохранить предыдущую release directory для rollback.
6. Переключить document root/symlink атомарно средствами hosting, если они доступны; иначе согласовать короткое maintenance window.

Git pull в live directory допустим только если заранее подтверждены чистое дерево, backup и возможность вернуться на предыдущий reviewed commit без destructive reset. Artifact deployment предпочтительнее.

## 5. Application commands

Все команды выполняются из проверенного application root. Для текущего hosting используется `php83`:

```bash
php83 artisan down --retry=60
php83 artisan migrate --force
php83 artisan storage:link
php83 artisan optimize:clear
php83 artisan config:cache
php83 artisan route:cache
php83 artisan view:cache
php83 artisan queue:restart
php83 artisan up
```

`storage:link` идемпотентно проверяется; существующий корректный link не удаляется. Maintenance можно не включать только для backward-compatible deployment после отдельной оценки миграций.

Если реальный async worker не используется текущим feature scope, `queue:restart` безопасно инвалидирует restart timestamp, но infrastructure status остаётся pending до настройки worker. Scheduler сейчас не содержит задач; при появлении расписания настроить cron `php83 artisan schedule:run` каждую минуту и зафиксировать владельца.

## 6. Smoke tests before opening indexing

- `/up` возвращает 200 и не раскрывает config/version/secrets;
- Home, About, Contacts, Blog, published article, Legal, Thanks, 404 и Genplan fallback возвращают ожидаемые statuses;
- `/robots.txt` содержит `Disallow: /`;
- canonical использует единственный HTTPS production origin;
- sitemap XML валиден и не содержит draft/private/query URLs;
- admin login и permissions работают;
- test Lead сохраняется в БД, затем удаляется/анонимизируется только по согласованной operational процедуре;
- public and admin assets загружаются, `public/storage` работает;
- map SDK requests равны 0;
- browser console, server log и network не содержат stack trace/PII/secrets;
- responsive smoke: 360, 390, 768, 1024, 1280, 1440.

## 7. Rollback decision

Rollback требуется при failed migrations, 5xx на основных routes, broken assets/storage, невозможности создать Lead, authorization regression или canonical/robots, открывающих неправильный origin.

Code rollback выполняется переключением на сохранённый предыдущий artifact/release. Migration rollback не выполняется автоматически: сначала оценить backward compatibility и backup. `php83 artisan migrate:rollback --step=N --force` допустим только для заранее проверенных reversible migrations и под контролем ответственного. При destructive/data migration восстановление выполняется в отдельной verified процедуре из backup.

После rollback повторить config/route/view cache, health и smoke tests. Не удалять текущий failed artifact и logs до завершения incident review.

## 8. Opening indexing

Только после PASS domain/DNS/SSL/origin/content/legal/sitemap/robots/security checks:

1. установить `SEO_INDEXING_ENABLED=true`;
2. `php83 artisan config:cache`;
3. проверить production `/robots.txt`, HTML meta robots и sitemap;
4. выполнить внутренний crawl;
5. отправить sitemap поисковым системам вручную.

HSTS включается отдельно после подтверждения полного HTTPS lifecycle. CSP остаётся documented debt до точного allowlist Filament/Vite и будущего map provider.

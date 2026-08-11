# Project State

## Current Release

Release 2 — Blog: **READY**.

Release 3 не начат. Главная, Leads и Genplan в рамках Release 2 не реализовывались.

## Completed Releases

### Release 0 — Foundation

- Дата: 2026-08-11.
- Laravel 12 / PHP 8.3+, Filament 4, Spatie Permission 8.
- Административная аутентификация, Users, Roles/Permissions, Policies, защита super-admin.
- Идемпотентная матрица RBAC и безопасная команда создания первого super-admin.
- Typed/cache-aware Settings Foundation.
- Раздельные admin/public assets, Vite/Tailwind, storage и базовые тесты.
- Исходная проверка Release 0: 14 tests / 67 assertions.
- Release 0–2 baseline commit: `974b360afc459e3760486835fcf71ec9a614426c` (`chore: establish RelaxLand baseline through Release 2`).

### Release 1 — Shared Frontend + Simple Pages

- Дата: 2026-08-11.
- Создан общий публичный Blade layout с header, desktop/mobile navigation, breadcrumbs, CTA/button, form input foundation и footer.
- Реализованы Contacts, legal index/detail, success/thanks и стандартная кастомная Laravel 404.
- Добавлен понятный Settings UI в Filament поверх существующей таблицы и `SettingsRepository`.
- Добавлена сущность `LegalDocument`, sanitization разрешённого HTML, PDF-ветка и Filament CRUD.
- Публичный frontend проверен в браузере на 360, 390, 768, 1024, 1280 и 1440 px.
- Итоговая проверка: 23 tests / 114 assertions.

### Release 2 — Blog

- Дата: 2026-08-11.
- Добавлены BlogCategory и BlogPost, публикационные статусы и отдельный `Domain/Blog`.
- Реализованы публичные listing/article, категории, поиск, месяц, сортировка, пагинация и empty states.
- Статья поддерживает ограниченные JSON-блоки: heading, rich text, list, image, wide image и gallery.
- Добавлены previous/next по стабильному порядку `published_at + id` только среди публичных статей.
- Добавлены SEO title/description fallbacks, canonical, OpenGraph и базовый Article JSON-LD.
- Добавлены Filament resources категорий и статей с server-side publication controls.
- Публичный Blog проверен в браузере на 360, 390, 768, 1024, 1280 и 1440 px.
- Итоговая проверка: 35 tests / 180 assertions.

## Current Architecture

- Laravel/Blade приложение находится в корне репозитория; документация — в `docs/`.
- Публичный frontend использует Blade, Vite, Tailwind 4 и собственный согласованный CSS-слой; SPA отсутствует.
- Filament остаётся отдельной административной панелью `/admin` и не импортируется публичным Vite bundle.
- Spatie Permission является единственным источником ролей и permissions.
- Используемые домены: `Domain/Users`, `Domain/Settings`, `Domain/Content`, `Domain/Blog`.
- Policies обеспечивают серверные проверки; видимость UI не заменяет авторизацию.
- Целевая БД — MySQL 8+; автоматические тесты и текущая проверка миграций используют SQLite.

## Installed Packages

- PHP: 8.5.9 в проверочном окружении, minimum проекта — 8.3.
- Laravel Framework: 12.65.0.
- Filament: 4.12.6.
- Spatie Laravel Permission: 8.3.0.
- Laravel Pint: 1.30.5.
- Node.js: 24.14.0.
- npm: 11.19.0.
- Vite: 7.3.6.
- Tailwind CSS: 4.3.3.

Filament Shield не установлен и не требуется текущей архитектуре.

## Database

- `users`, `password_reset_tokens`, `sessions` — authentication; `users.is_active` управляет доступом в admin.
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` — Spatie RBAC.
- `settings` — typed JSON-настройки с группой и флагом публичности.
- `legal_documents` — title, slug, type, sanitized HTML content или PDF path, version, publication state и timestamps.
- `blog_categories` — name, unique slug, sort order, active state и timestamps.
- `blog_posts` — category FK, unique slug, listing fields, Storage paths, structured JSON content, publication/SEO fields и timestamps.
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` — инфраструктура Laravel.

Таблицы Leads, Genplan, Plots и Surroundings не создавались.

## Roles & Permissions

Роли: `super-admin`, `content-manager`, `sales-manager`, `viewer`.

Release 1 не добавлял новые permissions. Используются существующие:

- `settings.view`, `settings.manage` для настроек сайта;
- `content.view`, `content.create`, `content.update`, `content.delete`, `content.publish` для LegalDocument;
- `admin.access` для входа в Filament.

Фактическая матрица находится в `RolePermissionRegistrar` и синхронизируется seed-командой.

## Implemented Modules

- Laravel Foundation — READY.
- Administrative Authentication — READY.
- Users — READY.
- Roles & Permissions — READY.
- Settings Foundation — READY.
- Site Settings UI — READY.
- Public shared frontend — READY.
- Contacts — READY.
- Legal Documents — READY.
- Success / 404 pages — READY.
- Blog — READY.
- Main page — NOT STARTED.
- Leads — NOT STARTED.
- Genplan / Plots / Surroundings — NOT STARTED.
- Full SEO module — NOT STARTED.

## Public Routes

- `home`: `GET /` — временно показывает Contacts до реализации утверждённой главной в Release 3.
- `contacts`: `GET /contacts`.
- `legal.index`: `GET /privacy`.
- `legal.show`: `GET /privacy/{slug}`.
- `success`: `GET /thanks`.
- `blog.index`: `GET /blog`.
- `blog.show`: `GET /blog/{slug}`.
- Неизвестные URL используют `resources/views/errors/404.blade.php` и сохраняют HTTP 404.

Пункт Blog в общем navigation config активирован. Для Genplan, Plots и других будущих разделов фиктивные routes/страницы не создавались.

## Filament

- `/admin/site-settings` — секционная форма контактов, соцсетей, маршрутов, презентации и footer; чтение и запись разделены permissions.
- `/admin/legal-documents` — CRUD документов с HTML rich editor или PDF, version, type, active/published state.
- `/admin/blog-categories` — категории, порядок, active state и количество статей.
- `/admin/blog-posts` — статьи, структурированные blocks, media, publication и SEO.
- Публикационные поля и действия дополнительно ограничены `content.publish`.
- Существующие `/admin/users` и `/admin/roles` сохранены.

## Important Frontend Components

- `resources/views/layouts/public.blade.php`.
- `site-header`, `mobile-menu`, `site-footer`, `breadcrumbs`, `button`.
- `components/form/input.blade.php` как foundation будущих форм.
- `blog-card`, `pagination`, `article-image` для публичного Blog.
- Settings передаются в header/footer/pages из одного typed/cached механизма.
- Mobile menu поддерживает keyboard Escape, focus trap, `aria-expanded`, блокировку фонового scroll и возврат фокуса.
- Видимый focus, semantic landmarks и reduced-motion предусмотрены в общем CSS.

## Important Architectural Decisions

- Существующая таблица `settings` и `SettingsRepository` расширены; параллельный механизм контактов не создан.
- Администратор редактирует именованные поля, а не произвольные key/value записи.
- HTML LegalDocument очищается whitelist-санитайзером при записи; публично доступны только active и опубликованные документы.
- Contacts использует provider-neutral presentation fallback карты, координаты и внешние route links. API key и постоянный map provider не выбирались в Release 1.
- Корневой маршрут временно показывает Contacts, чтобы не создавать фиктивную главную до Release 3.
- Не существующие будущие маршруты остаются неактивными в navigation config вместо fake pages.
- Article content хранится как фиксированный JSON-контракт и редактируется Filament Builder; решение зафиксировано в `docs/DECISIONS.md` (ADR-001).
- Общий `HtmlSanitizer` переиспользуется LegalDocument и rich text блоками Blog; второй независимый sanitizer не создан.
- Listing выбирает только необходимые поля без полного content JSON и eager-loads category.

## Protected / Existing Functionality

- `User::canAccessPanel()` требует `is_active` и `admin.access`.
- Policies/Gates нельзя заменять одной проверкой видимости меню.
- Роль `super-admin` нельзя изменять или удалять обычным admin flow.
- Нельзя удалить собственную учётную запись или последнего активного super-admin.
- Собственный super-admin не может снять роли или отключить активность через Filament.
- Seed ролей/permissions остаётся идемпотентным.
- Публичные assets не импортируют Filament CSS/JS.
- Неактивные/будущие LegalDocument не доступны публично.
- Draft, future posts и статьи неактивных категорий не доступны публично.
- `content.publish` защищён server-side: crafted Filament state не позволяет менять status/published_at без permission.

## Missing Design Assets

- Утверждённые Figma exports, Blog listing/article макеты, изображения и responsive image assets отсутствуют в репозитории и в материалах Release 1/2.
- Случайные изображения и сгенерированные замены не использовались.
- Реализована семантическая responsive-композиция по текстовой спецификации и существующей системе Release 1; визуальную сверку с исходными макетами нужно выполнить после их передачи.

## Known Technical Debt

- MySQL 8+ недоступен в текущем окружении (`127.0.0.1:3306`), поэтому миграции Release 0/1/2 проверены только на SQLite. Перед production обязателен `migrate:fresh --seed` на MySQL 8+.
- Постоянный картографический provider не выбран; Contacts использует безопасный presentation fallback без API key.
- Контакты, координаты, route links и юридические документы должны быть заполнены фактическими данными через Filament.
- Pixel-perfect сверка и подключение утверждённых изображений отложены до получения исходных макетов/assets.
- Полноценная responsive image optimization pipeline не входит в Release 2; media хранятся через Laravel Storage с MIME/size validation и alt в article blocks.
- Защищённый Git baseline Release 0–2 создан; `.env`, dependencies, production build и сгенерированные Filament assets исключены из истории.

## Tests

- Release 1: public pages/statuses, custom 404, LegalDocument visibility, HTML sanitization, PDF branch, Settings rendering.
- Admin: `settings.view`/`settings.manage`, content access and super-admin update flow.
- Release 2: visibility, category/search/date filters, pagination query preservation, previous/next, structured blocks, XSS, SEO и admin publication bypass protection.
- Полный Release 0 regression suite сохранён и проходит.

## Pending Work

- Следующий этап по SPEC — Release 3 Main Page, только после отдельного задания и получения утверждённых макетов/assets.
- Затем остаются Releases 4–9: Leads, Genplan foundation/interactions, Plots, Surroundings, production QA/SEO.
- До production: MySQL 8+ migration check, реальные settings/legal/blog data и визуальная сверка с макетами.

## Last Verification

- `composer validate --strict` — PASS.
- `composer audit --locked` — PASS, advisories отсутствуют.
- При повторной baseline-проверке 2026-08-11 endpoint Packagist security-advisories временно отвечал timeout/502; `composer.lock` после последнего успешного строгого аудита не изменялся, локальный `--ignore-unreachable` не выявил advisories.
- `npm audit --audit-level=moderate` — PASS, 0 vulnerabilities.
- `php artisan test` — PASS, 35 tests / 180 assertions.
- `php artisan migrate:fresh --seed` — PASS на SQLite.
- rollback двух Blog migrations, повторное применение и финальный fresh/seed — PASS на SQLite.
- `vendor/bin/pint` и `vendor/bin/pint --test` — PASS.
- `npm run build` — PASS, Vite 7.3.6.
- `php artisan view:cache` — PASS.
- Основные public/admin routes зарегистрированы и проверены feature-тестами.
- Browser QA Blog listing/article: 360/390/768/1024/1280/1440, без horizontal overflow и console errors.
- Browser QA Blog filters/search, semantic article, SEO metadata и previous/next — PASS.
- Mobile menu keyboard/focus flow — PASS.
- В публичных Vite assets нет ссылок на Filament — PASS.
- `.env`, `public/build`, `public/storage` игнорируются git — PASS.
- MySQL `127.0.0.1:3306` — UNAVAILABLE.

## Last Updated

2026-08-11 — завершён Release 2: Blog domain, structured content, Filament resources, public listing/article, filters, SEO, responsive/a11y QA и полный regression-check.

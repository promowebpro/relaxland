# Project State

## Current Release

Release 3A — Visual Integration & Design Audit: **IMPLEMENTED**.

Release 3 завершён: главная, Home content editor и Stories реализованы. Полученные утверждённые макеты из `docs/design/` проходят интеграцию и responsive-аудит в Release 3A. Leads и интерактивный Genplan не начинались.

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

### Release 3 — Main Page

- Дата: 2026-08-11.
- Корневой маршрут заменён настоящей SSR-главной с фиксированной композицией Hero, benefits, atmosphere, life scenarios, care, seasons, CTA, static genplan preview, purchase options, Stories, visit, developer и Blog preview.
- Добавлен singleton `HomePage`: отдельные именованные поля и пять ограниченных повторяемых JSON-коллекций; универсальный page builder и raw JSON UI не создавались.
- Добавлена самостоятельная сущность `Story`, Filament CRUD, сортировка, active visibility и server-side защита публикации через существующие content permissions.
- Добавлена Filament page `/admin/home` с тематическими sections/repeaters, media MIME/size validation и SEO/publication fields.
- Hero и коллекции используют responsive `<picture>`, eager loading только для hero и lazy loading ниже fold; при отсутствии утверждённых media показываются контролируемые CSS placeholders.
- Seasons работают как доступные tabs с ArrowLeft/ArrowRight/Home/End, SSR first state и reduced-motion support.
- Settings и Blog переиспользуются без дублирования; Genplan остаётся статичным preview с disabled CTA, Leads отсутствуют.
- Итоговая автоматическая проверка: 44 tests / 231 assertions.
- Release 3 functional commit: `a3abdc05d0dc4b9513e4e1d357830f97a2f38c22` (`feat: implement RelaxLand main page`).

### Release 3A — Visual Integration & Design Audit

- Дата: 2026-08-11.
- Полностью проинвентаризированы `docs/design/`; mapping и browser matrix зафиксированы в `docs/VISUAL_QA.md`.
- Общая палитра, typography hierarchy, gutters, spacing, controls, header, mobile menu и footer приведены к утверждённым макетам.
- Home, Blog, Article, Contacts, Privacy, Success и 404 визуально интегрированы без изменения существующих business rules.
- По `page.pdf` добавлена фиксированная SSR About-страница `/about`; она переиспользует Home/Settings data и не создаёт универсальный page builder.
- Пригодные фотографии извлечены только из design PDF, оптимизированы в WebP и используются как controlled fallbacks для незаполненных CMS media.
- Exact design font и standalone mascot 404 отсутствуют; применены документированные system-serif и typographic fallbacks.
- Browser QA выполнен на 360/390/768/1024/1280/1440 для всех major public pages, включая реальный Blog card и Article fixture; overflow, broken images и console errors отсутствуют.
- Leads, Genplan domain/interactions, Plots и Surroundings не начинались.
- Итоговая автоматическая проверка: 45 tests / 238 assertions.

## Current Architecture

- Laravel/Blade приложение находится в корне репозитория; документация — в `docs/`.
- Публичный frontend использует Blade, Vite, Tailwind 4 и собственный согласованный CSS-слой; SPA отсутствует.
- Filament остаётся отдельной административной панелью `/admin` и не импортируется публичным Vite bundle.
- Spatie Permission является единственным источником ролей и permissions.
- Используемые домены: `Domain/Users`, `Domain/Settings`, `Domain/Content`, `Domain/Blog`, `Domain/Home`.
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
- `home_pages` — singleton-контент главной: именованные поля, ограниченные JSON-коллекции, publication и SEO.
- `stories` — самостоятельные истории главной: media, текст, порядок, active state и timestamps.
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` — инфраструктура Laravel.

Таблицы Leads, Genplan, Plots и Surroundings не создавались.

## Roles & Permissions

Роли: `super-admin`, `content-manager`, `sales-manager`, `viewer`.

Release 1 не добавлял новые permissions. Используются существующие:

- `settings.view`, `settings.manage` для настроек сайта;
- `content.view`, `content.create`, `content.update`, `content.delete`, `content.publish` для LegalDocument, Blog, Home и Stories;
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
- About — READY.
- Blog — READY.
- Main page — READY.
- Leads — NOT STARTED.
- Genplan / Plots / Surroundings — NOT STARTED.
- Full SEO module — NOT STARTED.

## Public Routes

- `home`: `GET /` — настоящая SSR-главная Release 3.
- `about`: `GET /about`.
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
- `/admin/home` — именованные секции и повторяемые коллекции главной, SEO и публикация.
- `/admin/stories` — истории, media, сортировка и active visibility.
- Публикационные поля и действия дополнительно ограничены `content.publish`.
- Существующие `/admin/users` и `/admin/roles` сохранены.

## Important Frontend Components

- `resources/views/layouts/public.blade.php`.
- `site-header`, `mobile-menu`, `site-footer`, `breadcrumbs`, `button`.
- `components/form/input.blade.php` как foundation будущих форм.
- `blog-card`, `pagination`, `article-image` для публичного Blog.
- `responsive-image` и фиксированная `pages/home.blade.php` для главной.
- `pages/about.blade.php` как фиксированная композиция на существующих Home/Settings data.
- Settings передаются в header/footer/pages из одного typed/cached механизма.
- Mobile menu поддерживает keyboard Escape, focus trap, `aria-expanded`, блокировку фонового scroll и возврат фокуса.
- Видимый focus, semantic landmarks и reduced-motion предусмотрены в общем CSS.

## Important Architectural Decisions

- Существующая таблица `settings` и `SettingsRepository` расширены; параллельный механизм контактов не создан.
- Администратор редактирует именованные поля, а не произвольные key/value записи.
- HTML LegalDocument очищается whitelist-санитайзером при записи; публично доступны только active и опубликованные документы.
- Contacts использует provider-neutral presentation fallback карты, координаты и внешние route links. API key и постоянный map provider не выбирались в Release 1.
- Корневой маршрут показывает SSR-главную; Contacts остаётся самостоятельной страницей `/contacts`.
- Не существующие будущие маршруты остаются неактивными в navigation config вместо fake pages.
- Article content хранится как фиксированный JSON-контракт и редактируется Filament Builder; решение зафиксировано в `docs/DECISIONS.md` (ADR-001).
- Общий `HtmlSanitizer` переиспользуется LegalDocument и rich text блоками Blog; второй независимый sanitizer не создан.
- Listing выбирает только необходимые поля без полного content JSON и eager-loads category.
- Архитектура Home singleton/коллекций зафиксирована в `docs/DECISIONS.md` (ADR-002); Stories отделены от Home JSON.
- Home Blog preview выбирает только необходимые поля и eager-loads category; публичные visibility scopes не дублируются.

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

## Design Assets

- Утверждённые exports получены в `docs/design/`: Home, Blog, Article, Contacts, About, Privacy, Success, 404 и reference contact sheet будущего Genplan.
- В Release 3A макеты являются источником визуальной истины; существующие модели, routes, visibility scopes и server-side authorization остаются источником продуктовой и архитектурной истины.
- Случайные стоковые и сгенерированные замены не используются. Пригодные фотографии извлекаются только из предоставленных design PDF и оптимизируются для web.
- Отдельные исходники фирменного display-font, иллюстрации 404 и интерактивных слоёв Genplan не предоставлены; для них допускаются документированные контролируемые fallback-состояния.

## Known Technical Debt

- MySQL 8+ недоступен в текущем окружении (`127.0.0.1:3306`), поэтому миграции Release 0–3 проверены только на SQLite. Перед production обязателен `migrate:fresh --seed` на MySQL 8+.
- Постоянный картографический provider не выбран; Contacts использует безопасный presentation fallback без API key.
- Контакты, координаты, route links и юридические документы должны быть заполнены фактическими данными через Filament.
- Точный фирменный display-font и отдельный исходник иллюстрации 404 отсутствуют; используются системный serif fallback и типографическая композиция до передачи исходников.
- Полноценный production image pipeline с автоматическими AVIF/srcset-производными не внедряется в Release 3A; утверждённые PDF-фотографии готовятся в контролируемых WebP-размерах.
- Exact design display-font и standalone mascot 404 остаются ожидаемыми исходниками; текущее поведение описано в `docs/VISUAL_QA.md`.
- Защищённый Git baseline Release 0–2 создан; `.env`, dependencies, production build и сгенерированные Filament assets исключены из истории.

## Tests

- Release 1: public pages/statuses, custom 404, LegalDocument visibility, HTML sanitization, PDF branch, Settings rendering.
- Admin: `settings.view`/`settings.manage`, content access and super-admin update flow.
- Release 2: visibility, category/search/date filters, pagination query preservation, previous/next, structured blocks, XSS, SEO и admin publication bypass protection.
- Release 3: real root Home, Settings reuse, active/inactive Home and Stories, stable Story order, public-only Blog preview, fixed collection sanitization, Filament access и crafted publication bypass protection.
- Release 3A: About route, отсутствие будущих product routes, public visual regression на всех major pages и шести viewport widths.
- Полный Release 0 regression suite сохранён и проходит.

## Pending Work

- Следующий этап по SPEC — Release 4 Leads, только после отдельного задания.
- Затем остаются Releases 5–9: Genplan foundation/interactions, Plots, Surroundings, production QA/SEO.
- До production: MySQL 8+ migration check, реальные settings/legal/blog data, exact display-font и standalone 404 mascot при их передаче.

## Last Verification

- `composer validate --strict` — PASS.
- `composer audit --locked` — PASS, advisories отсутствуют.
- После временных timeout/502 Packagist повторный строгий `composer audit --locked` завершился успешно; advisories отсутствуют.
- `npm audit --audit-level=moderate` — PASS, 0 vulnerabilities.
- `php artisan test` — PASS, 45 tests / 238 assertions.
- `php artisan migrate:fresh --seed` — PASS на SQLite.
- rollback двух Home/Story migrations, повторное применение и финальный fresh/seed — PASS на SQLite.
- `vendor/bin/pint` и `vendor/bin/pint --test` — PASS.
- `npm run build` — PASS, Vite 7.3.6.
- `php artisan view:cache` — PASS.
- Основные public/admin routes зарегистрированы и проверены feature-тестами.
- Browser QA Blog listing/article: 360/390/768/1024/1280/1440, без horizontal overflow и console errors.
- Browser QA Blog filters/search, semantic article, SEO metadata и previous/next — PASS.
- Mobile menu keyboard/focus flow — PASS.
- Browser QA Release 3A: Home, About, Blog, Article, Contacts, Privacy, Success и 404 на 360/390/768/1024/1280/1440 — PASS; zero horizontal overflow, zero broken images, console errors отсутствуют.
- Browser interactions: mobile menu open/Escape close/body scroll lock и seasons tab state — PASS.
- В публичных Vite assets нет ссылок на Filament — PASS.
- `.env`, `public/build`, `public/storage` игнорируются git — PASS.
- MySQL `127.0.0.1:3306` — UNAVAILABLE.

## Last Updated

2026-08-11 — реализован Release 3A: approved design integration, extracted WebP assets, About, responsive browser matrix, interaction checks и полный regression suite; Release 4 не начинался.

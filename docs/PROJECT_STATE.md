# Project State

## Current Release

Release 6 — Genplan Interactions: **IMPLEMENTED**.

SSR `/genplan` и progressive JavaScript реализуют доступный выбор кварталов и инфраструктуры, независимые 2D/3D layers, карточки, URL deep links и History API без загрузки участков. Public Plot UI (Release 7) и полноценная Surroundings map (Release 8) не начинались.

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

### Release 4 — Leads / Sales Workflow

- Дата: 2026-08-13.
- Добавлен отдельный `Domain/Leads` с контролируемыми source/form type/status enum, нормализацией телефона, action создания, policy и выборкой допустимых ответственных.
- `POST /leads` принимает единую Blade-форму, выполняет server-side validation, CSRF, honeypot и rate limit 5 запросов в минуту на IP, после успеха перенаправляет на существующий `/thanks`.
- UTM сохраняется по session first-touch контракту: первое непустое значение каждого поддерживаемого UTM-поля не перезаписывается до завершения сессии.
- Обязательное согласие фиксируется временем, текущим публичным Personal Data Consent с fallback на Privacy Policy и строковым snapshot версии документа.
- Одна доступная modal/form infrastructure подключена к Header, mobile menu, Footer, Home visit/CTA, Contacts и About; телефонные ссылки остаются отдельными `tel:` действиями.
- Filament `/admin/leads` предоставляет list/view/edit status, assignment и manager comment без публичного create/delete; добавлены поиск, фильтры и диапазон дат.
- `sales-manager` ограничен `admin.access`, `leads.view`, `leads.update`; ответственный выбирается только среди активных пользователей с `leads.update`.
- Email/SMS notification намеренно отложены: production mail provider в проекте не настроен, а минимальный надёжный workflow уже обеспечивают БД и Filament.
- Итоговая автоматическая проверка: 63 tests / 343 assertions.

### Release 5 — Genplan Foundation

- Дата: 2026-08-13.
- Добавлен компактный `Domain/Genplan` с пятью Eloquent models, controlled status/category enums, visibility scopes, geometry validator и public query service.
- Genplan хранит 2D/3D и optional mobile backgrounds; в Foundation был введён normalized contract `0..1`, а ошибочное решение об общей geometry между режимами впоследствии заменено Release 5A.
- `polygon_data` — ordered JSON list минимум из трёх объектов ровно `{x, y}`; validation выполняется в Filament и повторно в model saving hooks.
- Публичный renderer преобразует geometry в безопасные SVG points внутри `viewBox 0 0 1000 1000`; arbitrary SVG/HTML из БД не поддерживается.
- Реализован SSR `/genplan`: Genplan/Surroundings tabs, 2D/3D switch, SVG Quarter/Infrastructure layers, минимальный Quarter selection/card и controlled empty states.
- Добавлены read-only API Resources: overview, Quarter, Quarter plots, Infrastructure и Surroundings; выборки используют active/visible scopes и selected fields.
- В Filament создана группа «Посёлок»: Генплан, Кварталы, Участки, Инфраструктура, Окружение. Polygon редактируется structured repeater, raw JSON UI отсутствует.
- `genplan.view/manage` и `plots.view/manage` применяются через Policies; существующая role matrix не расширяет права sales-manager. Parent delete запрещён при зависимостях, FK используют `RESTRICT`.
- Home preview и shared navigation ведут на активированный `genplan.index`; полный Genplan JS на Home не загружается.
- Geometry contract зафиксирован в `docs/DECISIONS.md` (ADR-005), design state mapping и browser matrix — в `docs/VISUAL_QA.md`.
- Итоговая автоматическая проверка: 77 tests / 436 assertions.

### Release 5A — Genplan Multi-view Geometry Correction

- Дата: 2026-08-13.
- Presentation geometry вынесена из business tables в typed `quarter_geometries`, `plot_geometries` и `infrastructure_point_geometries` с enum `mode = 2d|3d` и DB unique `entity_id + mode`.
- Forward migration переносит legacy geometry только в `3d`; неподтверждённая `2d` geometry не создаётся. Rollback восстанавливает legacy columns из `3d` records.
- Filament показывает отдельные structured sections «Геометрия 3D» и «Геометрия 2D»; raw JSON и отдельные mobile geometries отсутствуют.
- Public queries и API принимают controlled `mode`, по умолчанию используют `3d` и никогда не подставляют geometry другого режима.
- SSR содержит оба mode-specific SVG layer, стартует в `3d`; JS одновременно меняет background и geometry layer, а отсутствующий слой очищается.
- Mobile background разрешается только после явного compatibility confirmation для той же проекции/framing; без него используется desktop asset.
- RBAC скорректирован: базовый `viewer` больше не получает `leads.view` и не видит PII заявок; `sales-manager` сохраняет Leads workflow.
- Итоговая автоматическая проверка: 87 tests / 476 assertions.

### Release 6 — Genplan Interactions

- Дата: 2026-08-13.
- Добавлен единый state contract: `activeTab`, `mode`, `selectedQuarter`, `selectedInfrastructure`, `loading`, `error`, `incomplete`; default — Genplan/3D без выбранного объекта.
- SSR обрабатывает controlled query `view`, `mode`, `quarter`, `point`; Quarter и Infrastructure используют публичные slug, прямые ссылки не зависят от внутренних ID.
- History API синхронизирует click/keyboard selection, mode, tabs и Back/Forward; invalid/hidden/missing-mode значения fail closed без cross-mode fallback.
- SVG кварталы, эквивалентный текстовый selector и HTML-маркеры синхронизированы; marker target — 44×44 px, статусы передаются текстом, Enter/Space/Escape и возврат фокуса проверены.
- Quarter и Infrastructure cards взаимно исключаются; Quarter CTA переиспользует существующую consultation Lead modal. Участки и plot endpoint из публичной страницы не загружаются.
- Mobile selection card реализована как нижний non-modal sheet; desktop остаётся двухколоночным. Surroundings сохраняет provider-neutral foundation без внешней карты.
- Progressive enhancement сохраняет рабочие SSR links без JS; hydration использует уже выданный dataset, не повторяет API-запрос и очищает selection при отсутствии geometry нового режима.
- Доступность включает tab semantics, semantic controls, accessible names, текстовые статусы, visible focus, reduced-motion и отсутствие focus trap в немодальных cards.
- `infrastructure_points` получил стабильный per-Genplan public slug с migration/backfill, Filament field и controlled API field.
- Browser QA пройден на 360/390/768/1024/1280/1440 px (дополнительно 320 px); preflight 5A отдельно подтвердил намеренно разные 2D/3D координаты на 390/1280 px.
- Release 6 regression: 8 tests / 49 assertions; полный suite: 95 tests / 525 assertions.

## Current Architecture

- Laravel/Blade приложение находится в корне репозитория; документация — в `docs/`.
- Публичный frontend использует Blade, Vite, Tailwind 4 и собственный согласованный CSS-слой; SPA отсутствует.
- Filament остаётся отдельной административной панелью `/admin` и не импортируется публичным Vite bundle.
- Spatie Permission является единственным источником ролей и permissions.
- Используемые домены: `Domain/Users`, `Domain/Settings`, `Domain/Content`, `Domain/Blog`, `Domain/Home`, `Domain/Leads`, `Domain/Genplan`.
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
- `leads` — contact data, controlled source/form type, source page, session UTM, workflow status, nullable responsible user, manager comment и consent document snapshot; user/document deletion uses `SET NULL` without deleting the Lead.
- `genplans` — name/slug, 2D/3D/mobile Storage paths, source dimensions, explicit mobile compatibility flags, active state и reserved settings JSON.
- `quarters` — Genplan FK, per-Genplan unique slug, enum status, order и active state; presentation geometry вынесена отдельно.
- `plots` — Quarter FK, per-Quarter unique slug/number, decimal area/money, enum status, attributes и public visibility; presentation geometry вынесена отдельно.
- `infrastructure_points` — Genplan FK, per-Genplan public slug, controlled category, per-mode visibility, order и active state; presentation geometry вынесена отдельно.
- `quarter_geometries` — Quarter FK, typed mode, normalized polygon/label и unique `quarter_id + mode`.
- `plot_geometries` — Plot FK, typed mode, optional normalized polygon/marker и unique `plot_id + mode`.
- `infrastructure_point_geometries` — InfrastructurePoint FK, typed mode, normalized marker и unique `infrastructure_point_id + mode`.
- `surrounding_places` — provider-neutral controlled category, decimal latitude/longitude, description/external URL, order и active state.
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` — инфраструктура Laravel.

## Roles & Permissions

Роли: `super-admin`, `content-manager`, `sales-manager`, `viewer`.

Release 1 не добавлял новые permissions. Используются существующие:

- `settings.view`, `settings.manage` для настроек сайта;
- `content.view`, `content.create`, `content.update`, `content.delete`, `content.publish` для LegalDocument, Blog, Home и Stories;
- `admin.access` для входа в Filament.
- `leads.view` для списка и detail заявок;
- `leads.update` для status, assignment и manager comment.
- `genplan.view`, `genplan.manage` для Genplan, Quarter, InfrastructurePoint и SurroundingPlace;
- `plots.view`, `plots.manage` для Plot.

`sales-manager` получает только `admin.access`, `leads.view`, `leads.update`. `viewer` имеет read-only content/settings/genplan/plots permissions, но не получает `leads.view` и не имеет доступа к PII заявок; manage permissions автоматически никому не добавлялись. Super-admin сохраняет Gate-before полный доступ.

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
- Leads — READY.
- Genplan Foundation — READY.
- Multi-view Geometry — READY.
- Genplan Interactions — READY.
- Plot domain/admin/API foundation — READY; public Plot UI — NOT STARTED (Release 7).
- Surroundings domain/admin/API foundation — READY; full map — NOT STARTED (Release 8).
- Full SEO module — NOT STARTED.

## Public Routes

- `home`: `GET /` — настоящая SSR-главная Release 3.
- `about`: `GET /about`.
- `contacts`: `GET /contacts`.
- `legal.index`: `GET /privacy`.
- `legal.show`: `GET /privacy/{slug}`.
- `success`: `GET /thanks`.
- `leads.store`: `POST /leads` — единый endpoint публичных заявок; отдельной публичной страницы/GET API Leads нет.
- `blog.index`: `GET /blog`.
- `blog.show`: `GET /blog/{slug}`.
- `genplan.index`: `GET /genplan` — SSR interactions/empty state; controlled query: `view=surroundings`, `mode=2d|3d`, `quarter={public-slug}`, `point={public-slug}`.
- `api.genplan.*`: read-only `GET /api/genplan`, Quarter, Quarter plots и Infrastructure принимают controlled `mode=2d|3d` (default `3d`, invalid mode → 422); Surroundings geometry mode не использует.
- Неизвестные URL используют `resources/views/errors/404.blade.php` и сохраняют HTTP 404.

Пункты Blog и Genplan в общем navigation config активированы. Для Plots и других будущих public разделов фиктивные routes/страницы не создавались.

## Filament

- `/admin/site-settings` — секционная форма контактов, соцсетей, маршрутов, презентации и footer; чтение и запись разделены permissions.
- `/admin/legal-documents` — CRUD документов с HTML rich editor или PDF, version, type, active/published state.
- `/admin/blog-categories` — категории, порядок, active state и количество статей.
- `/admin/blog-posts` — статьи, структурированные blocks, media, publication и SEO.
- `/admin/home` — именованные секции и повторяемые коллекции главной, SEO и публикация.
- `/admin/stories` — истории, media, сортировка и active visibility.
- `/admin/leads` — newest-first список, поиск по name/phone/email, filters status/source/form type/manager/date, безопасный detail и ограниченное редактирование workflow-полей.
- `/admin/genplans`, `/admin/quarters`, `/admin/plots`, `/admin/infrastructure-points`, `/admin/surrounding-places` — группа «Посёлок» с typed fields, filters и structured geometry controls.
- Публикационные поля и действия дополнительно ограничены `content.publish`.
- Существующие `/admin/users` и `/admin/roles` сохранены.

## Important Frontend Components

- `resources/views/layouts/public.blade.php`.
- `site-header`, `mobile-menu`, `site-footer`, `breadcrumbs`, `button`.
- `lead-modal` — единственная reusable форма заявок с contextual source/form type/heading, progressive hash fallback, focus trap, Escape, focus restore и body scroll lock.
- `components/form/input.blade.php` как foundation будущих форм.
- `blog-card`, `pagination`, `article-image` для публичного Blog.
- `responsive-image` и фиксированная `pages/home.blade.php` для главной.
- `pages/about.blade.php` как фиксированная композиция на существующих Home/Settings data.
- `pages/genplan/index.blade.php` как SSR interaction shell; `resources/js/genplan/foundation.js`, `core/state.js`, `core/url-state.js` и `core/stage.js` реализуют controlled state, History API и доступную синхронизацию.
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
- Session first-touch UTM и snapshot юридического согласия являются долгосрочным Lead contract; решение зафиксировано в `docs/DECISIONS.md` (ADR-004).
- Normalized Genplan geometry `0..1`, view-specific `2d|3d` records, запрет cross-mode fallback, exact polygon shape и безопасный SVG mapping являются долгосрочным contract; решение зафиксировано в `docs/DECISIONS.md` (ADR-005).

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
- Public Lead creation не принимает status, assignment или manager comment; source/form type/status ограничены enum, source URL очищается до текущего host/path, PII не пишется в logs и не публикуется через API.
- Lead create/delete недоступны из Filament; update защищён Policy и повторной server-side проверкой допустимого ответственного.
- Публичный Genplan API read-only, не отдаёт inactive/hidden records, timestamps/settings/internal Plot attributes или admin models wholesale.
- Genplan→Quarter и Quarter→Plot используют `RESTRICT`; Policy скрывает delete для parent с зависимостями, bulk delete отключён.

## Design Assets

- Утверждённые exports получены в `docs/design/`: Home, Blog, Article, Contacts, About, Privacy, Success, 404 и Genplan state reference contact sheet.
- В Release 3A макеты являются источником визуальной истины; существующие модели, routes, visibility scopes и server-side authorization остаются источником продуктовой и архитектурной истины.
- Случайные стоковые и сгенерированные замены не используются. Пригодные фотографии извлекаются только из предоставленных design PDF и оптимизируются для web.
- Отдельные исходники фирменного display-font, иллюстрации 404 и production 2D/3D/mobile backgrounds/layers Genplan не предоставлены; для них допускаются документированные controlled states.

## Known Technical Debt

- MySQL 8+ недоступен в текущем окружении (`127.0.0.1:3306`), поэтому миграции Release 0–6 проверены только на SQLite. Миграции используют portable Laravel Schema/Query API, JSON, DECIMAL, FK и composite unique, но перед production обязателен полный migration/rollback check на MySQL 8+.
- Production mail transport/получатель не настроены; уведомления о новых Leads намеренно отложены вместо фиктивного mail flow. Заявка сохраняется и сразу доступна в Filament.
- Постоянный картографический provider не выбран; Contacts использует безопасный presentation fallback без API key.
- Контакты, координаты, route links и юридические документы должны быть заполнены фактическими данными через Filament.
- Точный фирменный display-font и отдельный исходник иллюстрации 404 отсутствуют; используются системный serif fallback и типографическая композиция до передачи исходников.
- Полноценный production image pipeline с автоматическими AVIF/srcset-производными не внедряется в Release 3A; утверждённые PDF-фотографии готовятся в контролируемых WebP-размерах.
- Exact design display-font и standalone mascot 404 остаются ожидаемыми исходниками; текущее поведение описано в `docs/VISUAL_QA.md`.
- Production 2D/3D/mobile Genplan backgrounds и реальные normalized polygons/markers должны быть переданы и заполнены через Filament; contact sheet не подменяет business data.
- Публичные Infrastructure slug рассчитаны как долгоживущие ссылки; их ручное изменение после публикации потребует redirect strategy.
- Защищённый Git baseline Release 0–2 создан; `.env`, dependencies, production build и сгенерированные Filament assets исключены из истории.

## Tests

- Release 1: public pages/statuses, custom 404, LegalDocument visibility, HTML sanitization, PDF branch, Settings rendering.
- Admin: `settings.view`/`settings.manage`, content access and super-admin update flow.
- Release 2: visibility, category/search/date filters, pagination query preservation, previous/next, structured blocks, XSS, SEO и admin publication bypass protection.
- Release 3: real root Home, Settings reuse, active/inactive Home and Stories, stable Story order, public-only Blog preview, fixed collection sanitization, Filament access и crafted publication bypass protection.
- Release 3A: About route, отсутствие будущих product routes, public visual regression на всех major pages и шести viewport widths.
- Release 4 public: создание/redirect, required/conditional validation, enum contracts, phone normalization, consent snapshot, safe page URL, first-touch UTM, honeypot, rate limit, repeat submissions и CTA integration.
- Release 4 admin: unauth/permission access, sales-manager list/view/update, read-only access только при явно выданном `leads.view`, enum status, eligible assignment и XSS-safe message/comment rendering.
- Release 5 domain: normalized coordinate boundaries, malformed/short polygons, deterministic SVG serialization, decimal money precision и enum rejection.
- Release 5 public/API: SSR/empty state, active Genplan, inactive Quarter, hidden Plot, mode-specific Infrastructure, inactive Surroundings, controlled DTO fields и read-only routes.
- Release 5 admin: unauthenticated/role access, view/manage separation, all five CRUD routes, invalid Repeater geometry и protected parent deletion.
- Release 5A: mode uniqueness/enum, независимые 2D/3D coordinates, отсутствие fallback, API default/validation, SSR layer clearing, dual-mode Filament save, mobile compatibility guard, forward/rollback legacy migration и отсутствие Leads у viewer.
- Release 6: SSR/query state, public slugs, invalid/hidden/missing-mode selection, mutual exclusion, no Plot/N+1 boundary, safe cards/API и browser keyboard/History interactions.
- Полный Release 0 regression suite сохранён и проходит.

## Pending Work

- Следующий этап по SPEC — Release 7 Public Plots, только после отдельного задания.
- Затем остаются Release 8 Surroundings map и Release 9 production QA/SEO.
- До production: MySQL 8+ migration check, реальные settings/legal/blog/Genplan data, 2D/3D/mobile plan assets, exact display-font и standalone 404 mascot при их передаче.

## Last Verification

- `composer validate --strict` — PASS.
- `composer audit --locked` — PASS, advisories отсутствуют.
- После временных timeout/502 Packagist повторный строгий `composer audit --locked` завершился успешно; advisories отсутствуют.
- `npm audit --audit-level=moderate` — PASS, 0 vulnerabilities.
- `php artisan test` — PASS, 95 tests / 525 assertions после Release 6.
- `php artisan migrate:fresh --seed` — PASS на SQLite.
- rollback двух Home/Story migrations, повторное применение и финальный fresh/seed — PASS на SQLite.
- Release 4 `migrate:fresh --seed`, rollback Leads migration и повторный migrate — PASS на SQLite; `assigned_to`/`privacy_document_id` используют `SET NULL`, обязательные индексы присутствуют.
- Release 5 `migrate:fresh --seed`, rollback `2026_08_13_150000_create_genplan_foundation_tables` и повторный migrate — PASS на отдельной SQLite verification DB; production/local data не очищались.
- Release 5A forward migration legacy→`3d`, отсутствие автоматического `2d`, rollback `3d`→legacy и повторный migrate — PASS в integration test и на отдельной SQLite verification DB.
- Release 6 public slug migration: forward/backfill/`NOT NULL`/per-Genplan unique, rollback и повторный migrate — PASS на SQLite; `migrate:fresh --seed` также PASS на `:memory:` verification DB.
- `vendor/bin/pint` и `vendor/bin/pint --test` — PASS.
- `npm run build` — PASS, Vite 7.3.6.
- `php artisan view:cache` — PASS.
- Основные public/admin routes зарегистрированы и проверены feature-тестами.
- Browser QA Blog listing/article: 360/390/768/1024/1280/1440, без horizontal overflow и console errors.
- Browser QA Blog filters/search, semantic article, SEO metadata и previous/next — PASS.
- Mobile menu keyboard/focus flow — PASS.
- Browser QA Release 3A: Home, About, Blog, Article, Contacts, Privacy, Success и 404 на 360/390/768/1024/1280/1440 — PASS; zero horizontal overflow, zero broken images, console errors отсутствуют.
- Browser interactions: mobile menu open/Escape close/body scroll lock и seasons tab state — PASS.
- Browser QA Release 4: reusable modal, context, validation, consent, keyboard/focus, success redirect, public CTAs и Filament Leads проверены на 360/390/768/1024/1280/1440; zero horizontal overflow и console errors.
- Browser QA Release 5 `/genplan`: 360/390/768/1024/1280/1440, zero image/SVG alignment delta, zero document overflow/broken images, minimum touch target 44px и empty state после удаления QA fixture.
- Browser interactions Release 5: 2D/3D background + marker visibility, pointer/keyboard Quarter selection, Genplan/Surroundings tabs, mobile menu Escape/focus — PASS; console warning/error log пуст.
- Browser QA Release 6: 360/390/768/1024/1280/1440 (дополнительно 320), default/2D/3D, Quarter/Infrastructure cards, close, mode-compatible persistence, missing geometry clear, tabs, Lead modal, direct links и Back/Forward — PASS; overflow и console warnings/errors отсутствуют.
- Release 5A preflight перед Release 6: distinct polygon/label/marker coordinates на 390/1280, coordinated background/layer switch и отсутствие fallback — PASS; найденный пробел Quarter label renderer исправлен.
- Filament browser QA: группа «Посёлок», пять CRUD listing/create routes и structured Quarter `x/y` Repeater — PASS.
- В публичных Vite assets нет ссылок на Filament — PASS.
- `.env`, `public/build`, `public/storage` игнорируются git — PASS.
- MySQL `127.0.0.1:3306` — UNAVAILABLE.

## Last Updated

2026-08-13 — Release 6 завершён: SSR/public-slug deep links, History API, доступные Quarter/Infrastructure interactions и cards, mode-safe selection, mobile bottom sheet, no-Plot performance boundary, ADR-006 и полная browser/test verification; Release 7 не начинался.

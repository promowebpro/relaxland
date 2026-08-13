# Release 3A — Visual QA

Дата проверки: 2026-08-11.

## Источники дизайна

| Файл | Назначение | Статус интеграции |
| --- | --- | --- |
| `docs/design/home.pdf` | Главная: hero, преимущества, цитаты, сценарии, seasons, genplan preview, purchase, stories, visit, developer, blog, footer | Реализовано |
| `docs/design/blog.pdf` | Листинг Blog, фильтры, карточки, пагинация | Реализовано |
| `docs/design/article.pdf` | Article header, cover, sidebar, content, previous/next | Реализовано |
| `docs/design/contacts.pdf` | Контакты, приглашение, карта, route card | Реализовано |
| `docs/design/page.pdf` | About / «О нас» | Реализовано новой фиксированной Blade-страницей `/about` |
| `docs/design/privacy.pdf` | Индекс юридических документов | Реализовано |
| `docs/design/success.pdf` | Success / thanks | Реализовано |
| `docs/design/404.pdf` | Custom 404 | Реализована типографическая композиция; отдельный mascot source отсутствует |
| `docs/design/genplan.png` | Reference contact sheet desktop/mobile Genplan states | В Release 5 используется как источник состояний и визуального shell; production background/layer assets в export отсутствуют |

## Принятый визуальный язык

- Canvas: `#ffffff` / `#f8f8f7`; основной текст `#30383d`; muted `#7b8083`; тёмные секции `#2b3b44`; pale-blue panels `#dce9ee`.
- Контентный контейнер: до `1280px`, адаптивные gutters `16–40px`.
- Основной шрифт: системный sans-serif stack. Для крупных цитат, wordmark и status headings используется системный `Georgia / Times New Roman` fallback: отдельный утверждённый display-font не предоставлен.
- Базовые радиусы изображений и panels: `10–12px`; organic media shapes применяются только в композициях, показанных в макетах.
- Buttons: компактные pill controls; white CTA на dark/photo backgrounds и dark CTA на light backgrounds.
- Все production-фотографии в `public/assets/design/` получены из предоставленных design PDF, оптимизированы в WebP и не являются stock/AI substitutions.

## Browser matrix

Проверка выполнена во встроенном браузере с явным viewport override. Для каждой ячейки проверялись: фактическая ширина viewport, наличие H1/header/footer, горизонтальный overflow, битые изображения и рендер страницы. `PASS` означает реальный browser-check, а не оценку по исходному CSS.

| Страница / ширина | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| Shared header/footer | PASS | PASS | PASS | PASS | PASS | PASS |
| Home `/` | PASS | PASS | PASS | PASS | PASS | PASS |
| About `/about` | PASS | PASS | PASS | PASS | PASS | PASS |
| Blog `/blog` | PASS | PASS | PASS | PASS | PASS | PASS |
| Article `/blog/{slug}` | PASS | PASS | PASS | PASS | PASS | PASS |
| Contacts `/contacts` | PASS | PASS | PASS | PASS | PASS | PASS |
| Privacy `/privacy` | PASS | PASS | PASS | PASS | PASS | PASS |
| Success `/thanks` | PASS | PASS | PASS | PASS | PASS | PASS |
| 404 | PASS | PASS | PASS | PASS | PASS | PASS |

## Проверенные состояния и интерактивность

- Home: approved hero photo, image benefits, dark quote, scenarios, care photo, seasons, static genplan preview, purchase cards, stories, visit, developer and Blog preview.
- Mobile navigation at `360px`: open state, `aria-expanded`, body scroll lock, Escape close and hidden state — PASS.
- Season tabs at `360px`: click updates `aria-selected`, active panel and hidden panels — PASS.
- Blog listing: temporary local published fixture used to verify an actual card and approved fallback cover at all six widths; fixture deleted after QA.
- Article: temporary local structured article used to verify real title, fallback cover, sidebar, content and layout at all six widths; fixture deleted after QA.
- Empty states remain controlled for an empty Blog, missing documents, unset Settings and missing CMS media.
- Browser console warning/error log after interaction checks: empty.
- Final browser checks: zero broken images and zero document-level horizontal overflow on all matrix pages and widths.
- Home screenshots were visually inspected at `360px` and `1280px`; Article was visually inspected at `1280px`.

## Ограничения и controlled fallbacks

- `MISSING DESIGN FONT`: exact display-font files/licence are absent. System serif fallback is intentionally used; no random webfont was downloaded.
- `MISSING 404 MASCOT SOURCE`: mascot in `404.pdf` is not available as a clean standalone raster/vector asset. The page uses the approved spacing, hierarchy and large typographic `404` without an invented illustration.
- `GENPLAN ASSET BOUNDARY`: `genplan.png` является contact sheet, а не отдельным production background/layer asset. Release 5 реализует shell и geometry architecture; фактические 2D/3D/mobile backgrounds должны быть загружены через Filament с одинаковым coordinate framing.
- Existing CMS-uploaded images take priority. Approved extracted WebP files are deterministic fallbacks when an editor has not uploaded page media.

## Automated verification

- `vendor/bin/pint --dirty` — PASS.
- `php artisan test` — PASS, 45 tests / 238 assertions.
- Production Vite build — PASS.
- Future public routes `/plots`, `/surroundings` — HTTP 404 in automated tests; `/genplan` активирован Release 5, а Leads использует только `POST /leads`.

# Release 5 — Genplan Foundation Visual QA

Дата проверки: 2026-08-13.

## Mapping состояний reference

| Состояние `docs/design/genplan.png` | Release 5 mapping |
| --- | --- |
| 3D overview | SSR 3D background, Quarter SVG layer и текстовый список кварталов |
| 3D + infrastructure | SVG marker layer с `show_on_3d`; marker data уже управляется из Filament/API |
| 2D plan | Базовый 2D/3D switch меняет background без дублирования geometry |
| Quarter selected | Минимальное progressive selection: synchronized polygon/list state и Quarter summary card |
| Quarter card | SSR карточка name/description/status; Plot CTA явно отложен до Release 7 |
| Genplan / Surroundings | Доступные tabs; Surroundings показывает provider-neutral placeholder и SSR list |
| Desktop | Двухколоночный stage/sidebar shell по reference hierarchy |
| Mobile | Одноколоночный shell, full-width controls, horizontal Quarter selector; подготовленный mobile asset используется только с тем же normalized framing |

Full hover/click choreography, animations, history/deep links, zoom/pan и полноценные инфраструктурные interactions относятся к Release 6 и в этой матрице имеют статус **N/A**.

## Browser matrix

Проверка выполнена на живом fixture (1 Genplan, 2 Quarter, 1 Plot, 2 InfrastructurePoint, 1 SurroundingPlace), созданном только на время QA и удалённом после неё. Для каждой ширины проверены H1/header/footer, background load, SVG/image bounding rectangles, document overflow, broken images, controls и touch target. `SVG alignment 0` означает нулевую разницу `x/y/width/height` между rendered image и overlay.

| Genplan Foundation / ширина | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| Foundation layout | PASS | PASS | PASS | PASS | PASS | PASS |
| SVG alignment | 0 | 0 | 0 | 0 | 0 | 0 |
| 2D/3D background switch | PASS | PASS | PASS | PASS | PASS | PASS |
| No document overflow | PASS | PASS | PASS | PASS | PASS | PASS |
| Broken images | 0 | 0 | 0 | 0 | 0 | 0 |
| Touch target minimum | 44px | 44px | 44px | 44px | 44px | 44px |
| Full Release 6 interactions | N/A | N/A | N/A | N/A | N/A | N/A |

## Проверенные состояния Release 5

- SSR `/genplan` остаётся содержательным без JS; при отсутствии active Genplan показывает controlled empty state и Lead CTA.
- 3D → 2D меняет image URL/alt, `aria-pressed` и marker visibility; geometry остаётся тем же SVG DOM.
- Quarter выбирается pointer или keyboard Enter/Space; polygon, list control и visible summary card синхронизированы.
- Genplan/Surroundings tabs поддерживают click, ArrowLeft/ArrowRight/Home/End, `aria-selected`, focus и panel visibility.
- Mobile menu на Genplan открывается, блокирует body scroll, закрывается Escape и возвращает focus.
- Mobile variant применяется только при его наличии; без него основной image сохраняет собственный aspect ratio и не искажается.
- Browser console warning/error log — пуст; document-level horizontal overflow и broken images отсутствуют.
- Filament группа «Посёлок» и все пять CRUD listing открыты; Quarter geometry редактируется structured repeater `x/y`, raw JSON UI отсутствует.

## Ограничения Release 5

- Сам contact sheet не используется как production background. До наполнения Genplan через Filament публичная страница закономерно показывает controlled empty state.
- Отдельные утверждённые production 2D/3D/mobile background assets и реальные polygons/markers ещё должны быть переданы и внесены редактором.
- Map provider для Surroundings не выбран; полноценная карта относится к Release 8.
- Full Genplan interactions остаются **N/A / Release 6**, а public Plot UI — **N/A / Release 7**.

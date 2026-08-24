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
- `GENPLAN ASSET BOUNDARY`: `genplan.png` является contact sheet, а не отдельным production background/layer asset. 2D и 3D имеют независимую geometry; каждый optional mobile background обязан сохранять проекцию и framing соответствующего desktop-режима и требует явного подтверждения совместимости.
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
| 2D plan | 2D/3D switch синхронно меняет background и отдельный mode-specific geometry layer |
| Quarter selected | Минимальное progressive selection: synchronized polygon/list state и Quarter summary card |
| Quarter card | SSR карточка name/description/status; Plot CTA явно отложен до Release 7 |
| Genplan / Surroundings | Доступные tabs; Surroundings показывает provider-neutral placeholder и SSR list |
| Desktop | Двухколоночный stage/sidebar shell по reference hierarchy |
| Mobile | Одноколоночный shell, full-width controls, horizontal Quarter selector; совместимый mobile asset переиспользует geometry своей проекции, отдельной mobile geometry нет |

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
- 3D → 2D меняет image URL/alt, `aria-pressed` и видимый mode-specific SVG group; предыдущий layer получает `hidden`, поэтому cross-mode geometry не остаётся на экране.
- Quarter выбирается pointer или keyboard Enter/Space; polygon, list control и visible summary card синхронизированы.
- Genplan/Surroundings tabs поддерживают click, ArrowLeft/ArrowRight/Home/End, `aria-selected`, focus и panel visibility.
- Mobile menu на Genplan открывается, блокирует body scroll, закрывается Escape и возвращает focus.
- Mobile variant применяется только при наличии explicit compatibility confirmation; без него renderer использует desktop asset в заявленном stage aspect ratio.
- Browser console warning/error log — пуст; document-level horizontal overflow и broken images отсутствуют.
- Filament группа «Посёлок» и все пять CRUD listing открыты; Quarter/Plot/InfrastructurePoint редактируют 3D и 2D geometry в отдельных structured sections, raw JSON UI отсутствует.

# Release 5A — Genplan Multi-view Geometry Correction Visual QA

Дата проверки: 2026-08-13.

Проверочный fixture намеренно использует разные координаты одного Quarter и InfrastructurePoint в `3d` и `2d`, а второй Quarter имеет только `3d`. Ожидаемый результат: переключение показывает координаты только выбранного режима; отсутствующий `2d` record не получает `3d` fallback.

| Release 5A / ширина | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| 3D background + 3D layer | PASS | PASS | PASS | PASS | PASS | PASS |
| 2D background + distinct 2D layer | PASS | PASS | PASS | PASS | PASS | PASS |
| Missing 2D geometry is cleared | PASS | PASS | PASS | PASS | PASS | PASS |
| Image/SVG rectangle delta | 0 | 0 | 0 | 0 | 0 | 0 |
| Document overflow / broken images | 0 / 0 | 0 / 0 | 0 / 0 | 0 / 0 | 0 / 0 | 0 / 0 |
| Minimum mode-control target | 44px | 44px | 44px | 44px | 44px | 44px |

Дополнительно проверены SSR default `3d`, native button keyboard activation, controlled mode state, separate Filament 2D/3D sections и пустой browser console warning/error log. QA fixture удалён после проверки; production data в репозиторий не добавлены.

## Ограничения Release 5

- Сам contact sheet не используется как production background. До наполнения Genplan через Filament публичная страница закономерно показывает controlled empty state.
- Отдельные утверждённые production 2D/3D/mobile background assets и реальные polygons/markers ещё должны быть переданы и внесены редактором.
- Map provider для Surroundings не выбран; полноценная карта относится к Release 8.
- Для исторического scope Release 5 Public Plot UI был **N/A / Release 7**; он закрыт отдельной матрицей ниже. Полноценная карта окружения остаётся **N/A / Release 8**.

# Release 6 — Genplan Interactions Visual QA

Дата проверки: 2026-08-13.

Перед реализацией повторён обязательный Release 5A preflight на fixture с намеренно разными polygon/label/marker coordinates в `3d` и `2d`. На 390 и 1280 px background, polygon, label и marker переключались совместно; был найден и закрыт пробел SSR-renderer: mode-specific Quarter label теперь выводится из `label_x/label_y`.

| Release 6 / ширина | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| No horizontal overflow | PASS | PASS | PASS | PASS | PASS | PASS |
| Quarter pointer + keyboard selection | PASS | PASS | PASS | PASS | PASS | PASS |
| Infrastructure 44×44 target + card | PASS | PASS | PASS | PASS | PASS | PASS |
| 2D/3D coordinated switch | PASS | PASS | PASS | PASS | PASS | PASS |
| Selection card composition | Bottom sheet | Bottom sheet | Bottom sheet | Sidebar | Sidebar | Sidebar |
| Text status / visible focus | PASS | PASS | PASS | PASS | PASS | PASS |

Дополнительно проверены: 320 px; default без selection; SSR deep links для Quarter/Infrastructure; mutual exclusion; очистка mode-incompatible selection; list/polygon hover/focus synchronization; Enter/Space; Escape и возврат фокуса; close control; consultation Lead source/type; URL query; Back/Forward; Genplan/Surroundings tabs; отсутствие iframe/provider; desktop fallback при неподтверждённом mobile asset; controlled mobile variant; browser warning/error log.

Plots не запрашиваются и в markup Release 6 не передаются. QA fixture после проверки удаляется; production content и изображения через эту проверку не подменяются.

# Release 7 — Public Plot Selection Visual QA

Дата проверки: 2026-08-17.

Контактный лист `docs/design/genplan.png` определяет shell, типографику, цвета и Quarter states, но не содержит финального Plot filter/card экрана. Поэтому Release 7 продолжает утверждённый визуальный язык контролами существующей системы: белые cards, тёмные pill actions, pale-blue detail panel, системный serif heading. Новая декоративная система, карта provider, booking или payment UI не добавлялись.

Проверочный локальный fixture содержит два Quarter, шесть Plot со статусами `available/reserved/sold`, намеренно разные 2D/3D polygons и один Plot без 2D geometry. Fixture нужен только для локального preview/QA; production data и новая migration им не создаются.

| Release 7 / ширина | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| No horizontal overflow | PASS | PASS | PASS | PASS | PASS | PASS |
| Plot list + filters | 1 col | 1 col | 2 col | 2 col | 2 col | 2 col |
| Plot card composition | Bottom sheet | Bottom sheet | Sidebar | Sidebar | Sidebar | Sidebar |
| Plot pointer/keyboard target | PASS | PASS | PASS | PASS | PASS | PASS |
| Text status + visible focus | PASS | PASS | PASS | PASS | PASS | PASS |
| Distinct 2D/3D geometry | PASS | PASS | PASS | PASS | PASS | PASS |

Проверены visible states: default без Plot payload; Quarter CTA «Выбрать участок»; SSR Quarter list; direct Plot URL; available card с inquiry CTA; reserved/sold card без CTA; status/area/price filters; default/price/area sort; controlled invalid range; empty results; loading; реальная network error при остановленном local server и успешный retry после запуска; быстрый 3D→2D conflict с итоговым применением только текущей geometry; text-only Plot без geometry и отсутствие cross-mode fallback.

Accessibility/interaction QA: list и SVG используют один `selectedPlot`; hover/focus синхронизированы; Enter/Space выбирают Plot; Escape закрывает Plot card до Quarter state; после DOM re-render focus возвращается на новый эквивалентный trigger; mobile targets не меньше 44 px; non-modal bottom sheet не создаёт второй focus trap; открытие существующей Lead modal скрывает mobile Plot sheet визуально и передаёт Quarter/Plot context в scoped hidden inputs.

URL/History QA: `quarter`, `plot`, filters и sort синхронизируются через History API; невалидная relationship не выбирает Plot; close/Escape удаляют только `plot`; Surroundings/Infrastructure очищают Plot state. Browser console warning/error log пуст, все шесть контрольных ширин без document overflow. Полный map provider остаётся Release 8.

# Release 8 — Surroundings Map Visual QA

Дата проверки: 2026-08-17.

Статус provider QA обновлён 2026-08-24: **DEFERRED** по продуктовому решению. Release 8A отменён до отдельного решения владельца проекта; тарифы и внешние map services на текущем этапе не подключаются. Интерактивная карта не считается production-ready, при этом SSR fallback является рабочим публичным состоянием, а существующие Yandex adapter/configuration сохранены.

Источник визуальной истины — Surroundings state в `docs/design/genplan.png`: desktop map/sidebar и mobile map/bottom sheet. `docs/design/contacts.pdf` дополнительно подтверждает язык map fallback и отдельной route card. Новые controls продолжают существующую систему: pale-blue shell, белые cards, тёмные pill actions и controlled category symbols; произвольные SVG/HTML icons из CMS не используются.

Локальный QA fixture содержит один active Genplan, координаты посёлка в существующих Settings и шесть active SurroundingPlace в пяти категориях. У всех мест отсутствует image, у одного отсутствует external URL — эти состояния проверены без broken image и пустого действия. Fixture находится только в локальной SQLite и не входит в migrations/seed/commit.

| Release 8 / ширина | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| No horizontal overflow | PASS | PASS | PASS | PASS | PASS | PASS |
| SSR list + category filters | PASS | PASS | PASS | PASS | PASS | PASS |
| Selection card composition | Bottom sheet | Bottom sheet | Sidebar | Sidebar | Sidebar | Sidebar |
| Close control target | 44×44 | 44×44 | 44×44 | 44×44 | 44×44 | 44×44 |
| Missing image / route URL | PASS | PASS | PASS | PASS | PASS | PASS |
| Missing-key fallback | PASS | PASS | PASS | PASS | PASS | PASS |

## Честное разделение результатов

- **SSR/progressive fallback — PASS.** Без key и без внешнего SDK доступны заголовок, координаты, present-category filters, шесть мест, straight-line distance labels, карточка, deep link и безопасные route links. Home и default `/genplan` SDK не загружают.
- **Provider adapter с test double — PASS.** Node test проверяет distinct settlement/place markers, controlled text DOM, click selection, aria state, bounds, zoom, category update без duplicate markers, destroy и удаление failed SDK scripts перед новой попыткой.
- **Негативный browser provider test — PASS.** Заведомо недействительный локальный key даёт controlled `provider_unavailable`, сохраняет список, показывает retry, не пишет warning/error в browser console и после трёх повторных tab cycles оставляет `0` failed SDK scripts.
- **Реальный map provider QA — DEFERRED.** SDK/key/referrer, tiles, vendor attribution, settlement/Place markers, provider viewport, gestures и network lifecycle не объявляются проверенными. Перед production владелец проекта отдельно выбирает платный Yandex Maps либо Leaflet/OpenStreetMap/другой provider; существующий adapter contract позволяет сделать это без миграции domain data.
- **Fallback regression — PASS.** Отсутствие API key/provider не вызывает white screen или uncaught error: SSR list/cards/categories/deep links, distance labels и route links остаются доступны, а интерфейс честно сообщает, что карта дополняет список.

## Проверенные interaction/state сценарии

- direct `/genplan?view=surroundings&place={slug}` восстанавливает карточку; invalid/inactive slug очищается без 500;
- произвольные query, `quarter`, `plot` и `point` не сохраняются рядом с Place; URL содержит только controlled `view + place`, internal IDs отсутствуют;
- выбор из списка синхронизирует item/card/URL, close сохраняет Surroundings и возвращает focus на trigger; browser Back закрывает selection;
- отключение категории выбранного места закрывает карточку; отключение всех категорий показывает empty state; «Показать все» восстанавливает шесть мест;
- повторные входы Genplan↔Surroundings не оставляют failed script или marker duplicates; stale async result защищён generation token;
- расстояние подписано «по прямой» и скрывается при отсутствии settlement point; missing route URL не создаёт пустую ссылку;
- mobile bottom sheet non-modal, map help сообщает про pinch zoom, однопальцевый scroll страницы не блокируется; reduced-motion используется для focus/scroll/map transitions;
- Browser QA выполнен на production Vite build. Финальная локальная вкладка оставлена на `http://127.0.0.1:8777/genplan?view=surroundings&place=mozhayskoe-more` в честном missing-key fallback.
- После решения о переносе provider 2026-08-24 тестовый origin `https://relaxland.evoline.digital` повторно проверен без API key: `/genplan?view=surroundings` показывает controlled `configuration_missing`, 6 SSR Place и category controls; SDK scripts `0`, retry скрыт, console warning/error `0`, document overflow отсутствует.

# Release 9 — Production Readiness & SEO Visual QA

Дата проверки: 2026-08-24.

Проверка выполнена на production Vite build в отдельной временной SQLite с `APP_ENV=production`, `APP_DEBUG=false`, explicit local canonical origin, `SEO_INDEXING_ENABLED=true` и `SURROUNDINGS_MAP_ENABLED=false`. Blog article и LegalDocument создавались только как QA fixtures; база удалена после проверки. Для наполненных Quarter/Plot/Place deep links дополнительно использованы существующие локальные fixtures без их изменения.

Визуальные источники `home.pdf`, `blog.pdf`, `article.pdf`, `contacts.pdf`, `page.pdf`, `privacy.pdf`, `success.pdf`, `404.pdf` и `genplan.png` повторно просмотрены. Release 9 сохраняет утверждённую композицию и добавляет metadata/operational boundaries, а не новую визуальную систему.

| Release 9 representative state / width | 360 | 390 | 768 | 1024 | 1280 | 1440 |
| --- | --- | --- | --- | --- | --- | --- |
| Проверенная страница | Home | Article | Legal detail | Surroundings fallback | Contacts | 404 |
| No horizontal overflow | PASS | PASS | PASS | PASS | PASS | PASS |
| Exactly one H1 | PASS | PASS | PASS | PASS | PASS | PASS |
| Broken images | 0 | 0 | 0 | 0 | 0 | 0 |
| Missing `alt` on rendered images | 0 | 0 | 0 | 0 | 0 | 0 |

Дополнительно на desktop проверены Home, About, Contacts, Blog root/article, Legal index/detail, Thanks, 404, Genplan overview, mode state и Surroundings state. Все 12 состояний имеют ожидаемые title/description/robots/canonical/OG; JSON-LD parsable, 404 canonical не получает, legal/thanks/error/query states не индексируются. Visual breadcrumbs и BreadcrumbList используют один controller payload.

Accessibility smoke: header/main/footer landmarks присутствуют, по одному H1, skip link и visible focus foundation сохранены; видимые поля Lead modal имеют связанные labels, а семь неразмеченных inputs в автоматическом подсчёте оказались только техническими `type=hidden`. Mobile Home 390 px и desktop Article 1280 px осмотрены по screenshot; layout соответствует design hierarchy. Browser console warning/error log пуст.

JavaScript-disabled эквивалент проверен по исходному HTTP SSR response до browser execution: Home H1/content, Blog article title/content, Surroundings heading/fallback и canonical metadata присутствуют. Progressive links доступны как обычные `href`. Browser backend не предоставляет отдельный persistent JavaScript-disable context, поэтому полноценный screen-reader/WCAG certification не заявляется; server-side regression дополнительно покрывает эти состояния.

Broken-link crawl проверил 14 unique internal links из representative pages: HTTP 4xx/5xx — `0`. Empty Blog/Legal/Genplan и missing media остаются controlled states. External validators, Search Console, Rich Results Test и реальные Core Web Vitals отложены до фактического HTTPS production origin.

Performance baseline: Home HTML 35.8 KB; основные empty/QA list/detail страницы 14.0–21.2 KB; наполненные Genplan deep links 63.2–73.8 KB. Vite build: CSS 126.48 KB / 24.67 KB gzip, JS 82.30 KB / 28.15 KB gzip. Query-bound regressions sitemap/Blog/Genplan проходят; Release 9 не добавляет provider/analytics requests. Эти числа являются локальной baseline, а не production Web Vitals.

Provider QA сохраняет статус **DEFERRED**. На Home, Genplan overview, query/deep-link и Surroundings fallback обнаружено `0` Yandex map SDK scripts/requests. Существующий adapter/configuration не удалён, но карта не объявляется production-ready.

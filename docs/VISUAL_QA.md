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
| `docs/design/genplan.png` | Reference contact sheet будущих desktop/mobile Genplan states | Только reference; интерактивный Genplan не входит в Release 3A |

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
- `GENPLAN REFERENCE ONLY`: `genplan.png` is a contact sheet of future interactive states, not a production preview asset. Release 3A retains a static aerial preview and disabled CTA; no Genplan domain, routes, filters or Plot logic were added.
- Existing CMS-uploaded images take priority. Approved extracted WebP files are deterministic fallbacks when an editor has not uploaded page media.

## Automated verification

- `vendor/bin/pint --dirty` — PASS.
- `php artisan test` — PASS, 45 tests / 238 assertions.
- Production Vite build — PASS.
- Future public routes `/leads`, `/genplan`, `/plots`, `/surroundings` — HTTP 404 in automated tests.

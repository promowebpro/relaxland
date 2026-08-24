# Release 9 — SEO Contract and Route Matrix

## Source of truth

SEO metadata формируется `App\Domain\Seo\SeoManager`. Canonical origin берётся только из `SEO_PUBLIC_URL` через `config/seo.php`; request Host и forwarded headers не могут изменить canonical. `SettingsRepository` остаётся единственным хранилищем управляемых глобальных значений.

Индексация управляется явным environment-флагом `SEO_INDEXING_ENABLED`. Новый environment должен начинать с `false`; включение выполняется только последним пунктом launch checklist после проверки контента, origin, SSL, robots и sitemap.

## Route matrix

| Route/state | HTTP | Robots при открытой индексации | Canonical | Sitemap | Structured data |
|---|---:|---|---|---|---|
| `/` | 200 | `index, follow` | `/` | Да | Organization, WebSite |
| `/about` | 200 | `index, follow` | `/about` | Да | Organization, WebSite, BreadcrumbList |
| `/contacts` | 200 | `index, follow` | `/contacts` | Да | Organization, WebSite, BreadcrumbList; ContactPoint/PostalAddress только из заполненных public Settings |
| `/blog` без query | 200 | `index, follow` | `/blog` | Да | Organization, WebSite, BreadcrumbList |
| `/blog/{published-slug}` | 200 | `index, follow` | stable article URL | Да | Organization, WebSite, BreadcrumbList, Article |
| `/blog` с `q`, `category`, `date`, `sort`, `page` или unknown query | 200 либо controlled 404 для invalid/overflow page | `noindex, follow` | `/blog` | Нет | базовые graph + BreadcrumbList |
| `/privacy` | 200 | `noindex, follow` | `/privacy` | Нет | базовые graph + BreadcrumbList |
| `/privacy/{public-slug}` | 200 | `noindex, follow` | stable legal URL | Нет | базовые graph + BreadcrumbList |
| `/thanks` | 200 | `noindex, nofollow` | `/thanks` | Нет | базовые graph без breadcrumb |
| `/genplan` без query | 200 | `index, follow` | `/genplan` | Да | Organization, WebSite |
| controlled Genplan query state (`mode`, `quarter`, `plot`, `point`, `view`, `place`, filters/sort) | 200, fail-closed selection | `noindex, follow` | `/genplan` | Нет | Organization, WebSite; transient selection schema отсутствует |
| invalid public entity | 404 | `noindex, nofollow` + `X-Robots-Tag` | отсутствует | Нет | отсутствует |
| custom 404 | 404 | `noindex, nofollow` + `X-Robots-Tag` | отсутствует | Нет | отсутствует |
| unexpected 500 | 500 | `noindex, nofollow` + `X-Robots-Tag` | отсутствует | Нет | отсутствует; standalone safe page |
| `/api/*` | 200/404/422/405 | `X-Robots-Tag: noindex, nofollow` | N/A | Нет | N/A |
| `/admin/*` | auth/403/redirect | `X-Robots-Tag: noindex, nofollow` | N/A | Нет | N/A |
| `/up` | 200 | `X-Robots-Tag: noindex, nofollow` | N/A | Нет | N/A |
| `/robots.txt` | 200 text | environment-aware | N/A | Нет | N/A |
| `/sitemap.xml` | 200 XML | technical endpoint | N/A | N/A | N/A |

`utm_*` не входит в canonical и не меняет индексируемость основной страницы: атрибуция сохраняется существующим first-touch middleware, а canonical остаётся чистым. Любой другой неизвестный query переводит Blog/Genplan состояние в `noindex` и не переносится в генерируемые pagination/state links.

## Title and description

Title priority:

1. published page/entity SEO title;
2. public entity title;
3. controlled route title;
4. global `seo.site_title` или `APP_NAME`.

Optional `seo.title_suffix` добавляется один раз. Если title уже равен suffix/site title или заканчивается suffix, повтор не создаётся. HTML удаляется, whitespace нормализуется, UTF-8 обрезается multibyte-safe до 70 символов.

Description priority:

1. page/entity SEO description;
2. public excerpt/summary;
3. global `seo.default_description`;
4. controlled short fallback.

HTML удаляется, whitespace нормализуется, лимит — 170 символов без разрыва multibyte. Admin/internal fields и Lead data не используются.

## Global Settings

Секция «SEO» существующей `/admin/site-settings` использует permissions `settings.view` / `settings.manage` и именованные поля:

- `seo.site_title`;
- optional `seo.title_suffix`;
- `seo.default_description`;
- optional `seo.default_og_image` (public Storage, JPEG/PNG/WebP, до 5 МБ);
- factual `seo.organization_name`;
- `seo.default_locale` (`ru_RU` или `en_RU`).

Social profiles переиспользуют существующие `social.*`, контакты и адреса — существующие `contacts.*`. Secrets, indexing switch, API keys и environment values в Settings не хранятся.

## Canonical and query policy

- Origin фиксирован `SEO_PUBLIC_URL`; production значение обязано быть HTTPS и совпадать с `APP_URL` host.
- Trailing slash удаляется web server для non-directory routes; root остаётся `/`.
- Article canonical всегда stable slug URL.
- Blog filters/search/sort/pagination не создают indexable landing pages. Explicit `page=1` получает root Blog canonical и `noindex`; malformed и overflow page возвращают 404.
- Genplan deep links ADR-006 сохраняются функционально и в History API, но canonical указывает на overview, а query states получают `noindex`.
- Invalid/hidden entity не получает canonical на несуществующий entity URL.
- Host header, `utm_*`, unknown query order и transient UI state canonical не меняют.

## Robots and sitemap

При `SEO_INDEXING_ENABLED=false` `/robots.txt` возвращает только `Disallow: /`, а все public HTML получают meta `noindex`. Host name не используется для определения environment.

При `true` robots разрешает public renderable assets/pages, закрывает `/admin`, `/api`, `/thanks`, `/up` и публикует absolute sitemap URL. Robots не является механизмом авторизации.

Sitemap включает только Home, About, Contacts, Blog listing, Genplan overview и опубликованные Blog articles активных категорий. Draft/future/inactive content, legal, thanks, admin, API, filters и Genplan selected states исключены. Article `lastmod` берётся из фактического `updated_at`; статические URL не получают искусственные даты. Ordering deterministic: статические URL, затем статьи по slug. Запрос один, eager loading не требуется. Application cache не используется; response требует revalidation, поэтому отдельная invalidation-система не нужна.

## OpenGraph and JSON-LD

Каждая HTML-страница получает escaped `og:title`, `og:description`, `og:type`, `og:url`, `og:site_name`, `og:locale`. `og:image` выводится только для существующего public Storage файла; broken path не публикуется. Article добавляет published/modified timestamps.

JSON-LD отдаётся одним безопасно encoded `@graph`:

- Organization с фактическим name/url, optional HTTPS `sameAs`, ContactPoint и PostalAddress только из заполненных public Settings;
- WebSite без фиктивного SearchAction;
- BreadcrumbList только там, где visual breadcrumb использует тот же массив;
- Article для опубликованной статьи.

Ratings, reviews, Offer/Product, availability, LocalBusiness hours, GeoCoordinates, Plot schema и вымышленные данные отсутствуют.

## Post-launch validation

После production content и открытия индексации вручную проверить Google Rich Results/Search Console и выбранные webmaster tools. Закрытый staging URL и private data не отправляются во внешние validators. Реальные Core Web Vitals фиксируются только после production measurement.

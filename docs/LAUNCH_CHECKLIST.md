# RelaxLand Launch Checklist

Дата аудита: 2026-08-24. Допустимые статусы: `PASS`, `PENDING CONTENT`, `PENDING INFRASTRUCTURE`, `DEFERRED`, `BLOCKER`.

| Area | Status | Evidence / required action |
|---|---|---|
| Application feature scope Release 0–9 | PASS | Laravel/Blade modules, SEO foundation and regression suite |
| Domain / canonical host | PENDING INFRASTRUCTURE | Утвердить production domain, www/non-www policy и `SEO_PUBLIC_URL` |
| DNS | PENDING INFRASTRUCTURE | Не изменялся в Release 9 |
| SSL | PENDING INFRASTRUCTURE | Выпуск/renewal и HTTPS redirect проверить на production |
| Production `.env` | PENDING INFRASTRUCTURE | Заполнить из safe `.env.example`, выполнить `app:production-check` |
| MySQL 8 verification | BLOCKER | Current local и staging проверки используют SQLite; нужен clean MySQL verification DB |
| Forward migrations | PASS | SQLite verification; MySQL pending отдельно |
| Backup provider | PENDING INFRASTRUCTURE | Runbook ready, provider/schedule/owner pending |
| Restore drill | PENDING INFRASTRUCTURE | Выполнить в изолированном environment |
| Public storage | PASS | Contract и staging storage link проверены; permissions повторить production |
| Mail delivery | PENDING INFRASTRUCTURE | Current mail=`log`, реальная доставка не подключена |
| Queue worker | PENDING INFRASTRUCTURE | Database queue configured; persistent worker на shared hosting не подтверждён |
| Scheduler | PASS | Приложение не содержит scheduled tasks; cron потребуется при их появлении |
| Logging / rotation | PENDING INFRASTRUCTURE | Настроить daily/info/14 days и доступ ответственному |
| Legal documents | PENDING CONTENT | Опубликовать фактические документы, версии и провести legal review |
| Privacy retention policy | PENDING CONTENT | Требует решения владельца/юриста; техническая consent snapshot готова |
| Public contacts/requisites | PENDING CONTENT | Заполнить только фактическими значениями через Settings |
| Home content/media | PENDING CONTENT | Заменить controlled fallbacks на утверждённые production media/text |
| Blog content | PENDING CONTENT | Опубликовать реальные статьи/categories |
| Genplan assets/geometries | PENDING CONTENT | Нужны production 2D/3D/mobile assets и normalized data |
| Plot commercial data | PENDING CONTENT | Проверить statuses, prices, visibility перед открытием |
| SEO global Settings | PENDING CONTENT | Site title, description, organization name, optional OG image |
| robots/sitemap application contract | PASS | Environment-aware robots и deterministic XML sitemap покрыты тестами |
| Search indexing | PENDING INFRASTRUCTURE | Оставить disabled до последнего post-deploy шага |
| Analytics/cookies decision | DEFERRED | Analytics provider/cookie banner не добавлялись |
| Interactive map provider | DEFERRED | `IMPLEMENTED — MAP PROVIDER DEFERRED`; SSR fallback ready |
| Admin users | PENDING INFRASTRUCTURE | Создать/проверить named production admins, удалить/запретить shared credentials |
| RBAC / viewer PII denial | PASS | Existing registrar/policies/targeted regression |
| Security headers | PASS | nosniff/referrer/permissions/frame; conditional HSTS; CSP explicit debt |
| CSP | DEFERRED | Составить точный allowlist после provider decision и Filament/Vite report-only QA |
| Dependency audit | PENDING INFRASTRUCTURE | npm production/full — 0 vulnerabilities; Composer lock — advisories не найдены с `--ignore-unreachable`, но Packagist timeout требует повторного online audit перед deployment |
| Performance baseline | PASS | Bundle/query/response baseline documented; real CWV pending production |
| Accessibility regression | PASS | Фактический keyboard/responsive audit; WCAG certification не заявляется |
| Responsive/browser QA | PASS | 360/390/768/1024/1280/1440, production build; repeat on production |
| Broken internal links | PASS | Local crawl; repeat after production content |
| Error pages / APP_DEBUG=false | PASS | Custom 404 and standalone safe 500 contract |
| Health endpoint | PASS | Laravel `/up`, no diagnostics/secrets; infrastructure monitoring pending |
| Deployment artifact/build | PENDING INFRASTRUCTURE | Shared hosting lacks Composer and current Node is insufficient; build externally |
| Rollback | PASS | Runbook defined; actual pre-deploy snapshot required each release |
| Production deployment | PENDING INFRASTRUCTURE | NOT STARTED by Release 9 |

Production deployment запрещён, пока существует `BLOCKER`. `PASS` для application contract не подтверждает внешнюю инфраструктуру, реальные данные или юридическую корректность контента.

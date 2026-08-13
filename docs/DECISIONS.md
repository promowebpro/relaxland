# Architecture Decisions

## ADR-001 — Article Content Storage

**Status:** Accepted, 2026-08-11.

### Context

Статья RelaxLand состоит не из одного HTML-поля: нужны заголовки, безопасный rich text, списки, одиночные и широкие изображения, а также галереи. После наполнения блога формат становится долгосрочным контрактом между Filament, БД и публичным renderer.

### Decision

`blog_posts.content` хранит упорядоченный JSON-массив блоков вида `{type, data}`. Разрешены только фиксированные типы `heading`, `rich_text`, `list`, `image`, `wide_image`, `gallery`. Структура каждого типа нормализуется `ArticleContentBlocks`; rich text проходит общий `HtmlSanitizer`, а пути изображений ограничены публичным каталогом `blog/`.

Filament Builder редактирует тот же ограниченный контракт. Публичный Blade renderer имеет явную ветку для каждого типа и не поддерживает arbitrary HTML blocks, Blade, JavaScript или пользовательские layout-компоненты.

### Why

- сохраняется порядок разнородных элементов статьи;
- форма остаётся понятной контент-менеджеру;
- публичный вывод детерминирован и безопасен;
- формат не превращается в универсальный page builder;
- файловые пути остаются совместимыми с Laravel Storage и будущим S3-диском.

### Alternatives considered

- Один HTML/RichEditor: недостаточно структуры для галерей и широких изображений.
- Отдельная таблица для каждого блока: сложнее редактирование, сортировка и миграции без текущей пользы.
- Универсальный page builder: выходит за границы SPEC и расширяет поверхность XSS/layout-ошибок.
- Markdown: не покрывает управляемые media layouts без собственного расширения формата.

### Consequences

- Добавление или изменение block type требует согласованного изменения sanitizer, Filament form, renderer и тестов.
- Миграции существующего JSON понадобятся, если контракт блока станет несовместимым.
- Оптимизация/варианты изображений могут быть добавлены внутри image data без хранения binary в БД.
- Формат предназначен только для BlogPost и не используется как общий конструктор страниц.

## ADR-002 — Fixed Home Content Model

**Status:** Accepted, 2026-08-11.

### Context

Главная RelaxLand состоит из заранее утверждённой последовательности секций. Редактору нужны понятные именованные поля, responsive media и сортируемые элементы внутри преимуществ, сценариев, сервиса, сезонов и вариантов приобретения. При этом главная не должна становиться универсальным page builder, одним HTML-полем, огромным JSON-документом или набором из сотни Settings-ключей.

### Decision

`home_pages` хранит один редактируемый экземпляр фиксированной страницы. Заголовки, тексты, SEO и основные media представлены отдельными колонками. Только явно повторяемые коллекции хранятся в пяти самостоятельных JSON-колонках с узкими контрактами: `benefits`, `life_scenarios`, `care_items`, `seasons`, `purchase_options`.

`HomeContent` удаляет неизвестные ключи, HTML и ограничивает размер коллекций/строк при каждой записи. Filament показывает эти контракты как именованные Sections и Repeaters без доступа к сырому JSON. Флаг публикации защищён существующим `content.publish` server-side.

Истории не входят в JSON главной: `Story` является отдельной сортируемой сущностью с media и `is_active`. Глобальные контакты, маршрут, документы и footer по-прежнему берутся из существующего Settings-механизма; превью блога — из существующего Blog domain.

### Why

- композиция и порядок секций остаются стабильными и соответствуют SPEC;
- редактор управляет содержимым без знания HTML или JSON;
- повторяемые небольшие коллекции не требуют пяти искусственных доменных таблиц;
- Stories и Blog сохраняют самостоятельный жизненный цикл и visibility rules;
- серверный Blade renderer остаётся детерминированным, доступным и SEO-friendly.

### Alternatives considered

- Универсальный page builder: избыточен для фиксированного макета и расширяет поверхность layout/XSS-ошибок.
- Один большой JSON: затрудняет валидацию, миграции и точечное редактирование.
- Один HTML/RichEditor: не обеспечивает responsive media и интерактивные доступные состояния.
- Все поля в Settings: размывает типизированный singleton и создаёт большое число несвязанных ключей.
- Отдельная таблица для каждого короткого списка: добавляет модели, policies и CRUD без текущей бизнес-ценности.

### Consequences

- Изменение состава секций требует миграции, обновления Filament form, Blade и тестов.
- Контракты JSON-коллекций являются частью модели данных и меняются совместимо.
- До передачи утверждённых media публичный renderer показывает контролируемые CSS placeholders, а не случайные изображения.
- Интерактивный Genplan, Leads и произвольная перестройка страницы сознательно остаются за пределами Release 3.

## ADR-003 — Approved Design Assets and Fixed About Page

**Status:** Accepted, 2026-08-11.

### Context

Release 3A получил утверждённые PDF-макеты и contact sheet Genplan, но не получил отдельный package исходных фотографий, exact display-font или standalone 404 mascot. Макет `page.pdf` однозначно описывает About, тогда как в текущей архитектуре отдельной About-сущности нет.

### Decision

Технически пригодные фотографии извлекаются только из предоставленных design PDF, оптимизируются в tracked WebP assets и используются как deterministic public fallbacks. CMS media из существующих моделей всегда имеет приоритет. Случайные stock/AI images и скачанные без подтверждения webfonts не используются.

About реализуется фиксированным Blade route `/about`: страница переиспользует опубликованный `HomePage` и typed Site Settings, а дополнительные композиционные подписи остаются частью фиксированного шаблона. Новая таблица, универсальный page builder и отдельный Filament resource не создаются.

Для отсутствующих exact display-font и 404 mascot применяются documented controlled fallbacks. `genplan.png` остаётся reference будущих интерактивных состояний и не используется как production UI или основание для преждевременного Genplan domain.

### Why

- визуальная интеграция опирается только на утверждённые материалы;
- незаполненный CMS сохраняет дизайн без подмены business data;
- About остаётся простой страницей в масштабе текущего релиза;
- архитектура Release 0–3 и граница Release 4/5 не размываются.

### Consequences

- Переданный позже exact font или mascot можно заменить локально без изменения модели данных.
- Изменение структуры About требует правки фиксированного Blade/CSS, но не миграции универсального контента.
- Извлечённые assets должны сохранять связь с design source и проходить web-оптимизацию.
- Полноценные Genplan interactions и content model остаются отдельным будущим релизом.

## ADR-004 — Lead Attribution and Consent Snapshot

**Status:** Accepted, 2026-08-13.

### Context

Пользователь может открыть сайт с UTM-метками, перейти на другую страницу и только затем отправить заявку. Одновременно заявка должна сохранять не только текущую ссылку на юридический документ, но и версию документа, с которой пользователь согласился: активная публикация позднее может измениться или быть удалена.

### Decision

Первое непустое значение каждого параметра `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term` сохраняется в Laravel session. Значение не перезаписывается до завершения сессии и копируется в Lead при создании. Cookies и внешняя аналитика для этого контракта не используются.

При создании Lead фиксируются `consent_given_at`, ссылка на текущий публичный `PersonalDataConsent` (с fallback на `PrivacyPolicy`) и строковая `privacy_document_version`. Удаление документа обнуляет только внешний ключ; сохранённая версия и Lead остаются. Если подходящий опубликованный документ ещё не заполнен, обязательное согласие и его время всё равно сохраняются, а ссылка формы ведёт на существующий индекс юридических документов.

### Why

- источник обращения не теряется при внутренней навигации;
- first-touch контракт прост, детерминирован и не требует внешнего сервиса;
- версия согласия сохраняется независимо от дальнейшего жизненного цикла документа;
- модуль не превращается в analytics или legal audit platform.

### Consequences

- UTM хранится только в пределах текущей сессии и не является cross-device attribution;
- каждое UTM-поле фиксируется при первом собственном непустом значении;
- изменение набора атрибуции или правил выбора документа требует совместимого изменения middleware, Lead action и тестов;
- полноценный юридический аудит, CRM-синхронизация и внешняя аналитика остаются вне Release 4.

## ADR-005 — Genplan Coordinate and Geometry Contract

**Status:** Accepted, corrected by Release 5A on 2026-08-13.

### Context

Quarter, Plot и InfrastructurePoint являются едиными бизнес-сущностями, но 2D и 3D backgrounds показывают разные проекции одного генплана. Одинаковая normalized точка не обязана обозначать тот же объект в перспективной 3D-проекции и ортографической 2D-проекции. Поэтому бизнес-данные должны оставаться общими, а presentation geometry — принадлежать конкретному режиму. Координаты обязаны переживать responsive resize, не зависеть от CSS pixels и оставаться пригодными для будущего visual editor, API и SVG interactions. Хранение готового SVG из административного ввода расширило бы поверхность XSS и сделало формат разметки частью данных.

### Decision

Основной контракт всех точек Genplan — нормализованные координаты в диапазоне `0..1`. `x = 0` / `y = 0` обозначают левую / верхнюю границу coordinate canvas, `x = 1` / `y = 1` — правую / нижнюю. Контракт одинаков для режимов, но значения независимы: Quarter, Plot и InfrastructurePoint хранят geometry records с обязательным typed `mode = 2d|3d` и уникальной парой `entity_id + mode`.

Публичный renderer переводит geometry выбранного режима во внутренний безопасный SVG `viewBox="0 0 1000 1000"`. Background image и соответствующий overlay занимают один и тот же stage rectangle; поэтому масштабирование выполняется браузером одинаково и не требует CSS-pixel offsets. При переключении renderer скрывает предыдущий geometry layer и не выполняет cross-mode fallback. Optional mobile asset допустим только после явного подтверждения, что он сохраняет проекцию и coordinate framing своего desktop-режима. Если mobile asset отсутствует, renderer использует desktop asset и сохраняет объявленный aspect ratio.

### Coordinate system

- `x`, `y`: конечные numeric values, включительно от `0` до `1`;
- постоянная точность хранения: до 6 знаков после запятой (`DECIMAL(7,6)` для marker/label columns);
- latitude/longitude SurroundingPlace не относятся к Genplan canvas и хранятся отдельно как географические decimal coordinates;
- CSS pixels и display размеры никогда не записываются как доменная геометрия.

### Polygon format

`polygon_data` — упорядоченный JSON list минимум из трёх точек:

```json
[
  {"x": 0.1234, "y": 0.4567},
  {"x": 0.2234, "y": 0.5567},
  {"x": 0.3234, "y": 0.4567}
]
```

Каждая точка содержит ровно ключи `x` и `y`; неизвестные и отсутствующие ключи запрещены. На записи модель повторно нормализует структуру независимо от Filament validation. Self-intersection и топологические операции сознательно не входят в foundation.

### Why

- одна бизнес-сущность имеет независимые presentation geometries для 2D и 3D;
- responsive layout переиспользует geometry текущей проекции без отдельных mobile coordinates;
- данные детерминированно сериализуются в API и SVG points;
- future editor может менять UI без миграции DB-контракта;
- server-side validation предотвращает malformed geometry даже вне Filament;
- приложение генерирует разрешённые SVG elements само и не исполняет markup из БД.

### Alternatives considered

- CSS/display pixels: ломаются при resize и при замене исходного изображения.
- Координаты исходного растра: требуют постоянной привязки к конкретным dimensions и усложняют mobile variants.
- Готовый SVG/path markup в БД: unsafe, плохо валидируется и связывает данные с renderer implementation.
- Общая геометрия для 2D и 3D: не описывает один объект в разных проекциях и приводит к визуально неверным overlay.
- Автоматическая homography/perspective transformation: требует проверенных control points и отдельного математического контракта, которых в Foundation нет; угадывание преобразования опаснее отсутствующей geometry.
- Полное дублирование Quarter/Plot/InfrastructurePoint по режимам: разрывает единую бизнес-сущность и дублирует статусы, цены и контент.
- Raw JSON map `{"2d": ..., "3d": ...}` в business table: скрывает mode/FK/uniqueness от relational constraints и усложняет typed validation; выбраны отдельные geometry records.
- Canvas/WebGL: лишает Foundation доступного DOM и не нужен для текущего объёма.

### Consequences

- 2D и 3D desktop backgrounds имеют собственные geometry records;
- mobile variant должен сохранять framing соответствующего режима; отдельные mobile geometries не создаются;
- сущность без geometry выбранного режима не получает geometry другого режима: API возвращает `null`, а SSR/JS очищают слой и используют controlled incomplete state там, где это необходимо;
- graphic polygon editor позже обязан читать и сохранять тот же normalized list;
- SVG scale `1000` является внутренней деталью renderer, а не новым DB-контрактом;
- сложная geometry validation может быть добавлена отдельно без изменения формата;
- mobile vertical composition используется только при наличии специально подготовленного mobile asset.

### Migration implications

- foundation migration из Release 5 создавала legacy normalized geometry непосредственно в business tables;
- корректирующая migration Release 5A создаёт `quarter_geometries`, `plot_geometries`, `infrastructure_point_geometries`, переносит legacy geometry **только в `3d`** и удаляет legacy columns;
- копирование legacy geometry одновременно в `2d` и `3d` запрещено: такой перенос ложно подтверждал бы корректность обеих проекций;
- rollback восстанавливает legacy columns из `3d` records, после чего удаляет view-specific geometry tables;
- будущий импорт pixel-based исходников должен явно делить `x` на source width и `y` на source height до записи;
- смена coordinate contract потребует versioned data migration для всех Quarter/Plot/InfrastructurePoint records и синхронного обновления API/renderer;
- произвольный SVG нельзя переносить в `polygon_data` без предварительного безопасного преобразования в список точек.

## ADR-006 — Public Genplan URL State

**Status:** Accepted, 2026-08-13.

### Context

Выбор режима, квартала или инфраструктуры должен открываться прямой ссылкой, корректно работать при SSR, восстанавливаться браузерными Back/Forward и не раскрывать внутренние database IDs. Одновременно Quarter и Infrastructure cards являются взаимоисключающими состояниями, а переход на Surroundings не должен сохранять скрытую selection.

### Decision

Публичный URL `/genplan` использует controlled query contract: `mode=2d|3d`, `quarter={quarter.slug}`, `point={infrastructure_point.slug}` и `view=surroundings`. Default state — `genplan + 3d` без selection. Quarter имеет приоритет, только если его slug опубликован и у него есть geometry текущего режима; иначе может быть выбран валидный point. Невалидные, массивные, inactive, hidden и mode-incompatible значения очищаются. При смене режима selection сохраняется только при наличии разрешённой geometry новой проекции.

`InfrastructurePoint` получает стабильный per-Genplan public slug с unique constraint и migration backfill. Сервер разрешает initial state из уже eager-loaded public collections. Клиент использует тот же state shape, `pushState` для действий и `popstate` для Back/Forward; API roundtrip при hydration не выполняется.

### Why

- ссылка воспроизводит видимое состояние уже в первом SSR response;
- публичные идентификаторы остаются стабильными при переносе данных;
- взаимное исключение Quarter/Infrastructure определено один раз;
- нет flash default-режима и нет лишнего network/N+1 запроса;
- прогрессивные links сохраняют навигацию без JavaScript.

### Consequences

- изменение публичного slug после публикации может сломать сохранённые ссылки и требует redirect strategy;
- URLs с `quarter` и `point` одновременно канонизируются в одно выбранное состояние;
- Surroundings очищает selection и не загружает map provider;
- Release 7 может расширить contract участком отдельным параметром только через новую совместимую decision/migration;
- URL не является storage: loading/error остаются transient client state.

# RelaxLand Backup and Restore Runbook

Статус: **RUNBOOK READY / PROVIDER PENDING**. Реальный backup provider, расписание и restore drill ещё не подтверждены.

## Scope

Backup обязан независимо сохранять:

- MySQL database;
- `storage/app/public` uploaded media;
- release identifier и migration status;
- отдельную защищённую возможность восстановить `.env`/secrets через approved secret store.

`.env` и secrets не помещаются в repository, public storage или обычный release archive. Application cache, sessions, compiled views, dependencies и `public/build` восстанавливаются из release artifact и не являются primary backup data.

## Policy recommendation

- database: daily, дополнительно pre-deploy snapshot;
- uploaded media: daily incremental + weekly full;
- retention: 7 daily, 4 weekly, 6 monthly — подтвердить владельцем и юридической политикой;
- encryption at rest and in transit;
- least-privilege access с отдельным backup operator;
- backup storage вне live application account;
- ежемесячная автоматическая integrity check и квартальный restore drill в изолированной verification environment.

Это рекомендация, не утверждённая retention/legal policy.

## Safe backup procedure

1. Подтвердить production host, database name и timestamp; значения credentials не печатать.
2. Создать versioned pre-deploy snapshot через approved hosting/control-panel backup или `mysqldump` с credentials из защищённого client config, не из command arguments.
3. Архивировать только `storage/app/public` с сохранением relative paths и permissions metadata.
4. Записать checksum, size, created-at, release commit и responsible person в private backup inventory.
5. Проверить, что destination не находится внутри public web root.
6. Проверить читаемость archive/listing без извлечения поверх live data.

Runbook намеренно не содержит production credentials и готовой destructive restore command.

## Restore drill

Restore выполняется сначала только в новой изолированной directory и отдельной verification DB:

1. Получить письменное подтверждение target environment и backup ID.
2. Убедиться, что verification target не совпадает с production DB/storage path.
3. Развернуть соответствующий release artifact.
4. Восстановить database через hosting tool или approved client config.
5. Восстановить media в пустой verification storage directory.
6. Применить только необходимые forward migrations командой `php artisan migrate --force`.
7. Выполнить integrity checks: row counts без вывода PII, migration status, sampled media checksums, public visibility scopes, admin authorization.
8. Пройти smoke tests Leads/Blog/Legal/Genplan и проверить отсутствие PII в logs.
9. Удаление verification environment выполняется владельцем инфраструктуры после явной проверки точного target; runbook не автоматизирует recursive delete.

## Production recovery

Перед восстановлением production:

- назначить incident lead;
- остановить write traffic или включить maintenance;
- сделать forensic snapshot текущего failed state;
- определить acceptable data-loss window;
- выбрать code rollback либо data restore;
- подтвердить mapping release ↔ schema ↔ backup;
- получить отдельное approval на каждый destructive/overwrite шаг.

После восстановления: config/route/view cache, storage link, queue restart, `/up`, major routes, Lead creation, authorization и logs. Indexing остаётся закрытой, пока smoke checklist не завершён.

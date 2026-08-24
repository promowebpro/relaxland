<?php

namespace App\Domain\Operations;

use App\Domain\Seo\CanonicalUrl;

final class ProductionReadiness
{
    public function __construct(private readonly CanonicalUrl $canonicalUrl) {}

    /** @return list<array{check: string, status: string, note: string}> */
    public function checks(): array
    {
        $configuredOrigin = trim((string) config('seo.public_url'));
        $configuredParts = parse_url($configuredOrigin);
        $origin = $this->canonicalUrl->origin();
        $originHost = parse_url($origin, PHP_URL_HOST);
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $productionOrigin = str_starts_with($origin, 'https://')
            && is_string($originHost)
            && ! str_ends_with($originHost, '.example')
            && ! in_array($originHost, ['localhost', '127.0.0.1'], true)
            && is_array($configuredParts)
            && in_array($configuredParts['path'] ?? '', ['', '/'], true)
            && ! isset($configuredParts['query'], $configuredParts['fragment'], $configuredParts['user'], $configuredParts['pass']);

        return [
            $this->check('Environment', app()->environment('production'), 'APP_ENV должен быть production.'),
            $this->check('Debug', ! config('app.debug'), 'APP_DEBUG должен быть false.'),
            $this->check('Application key', filled(config('app.key')), 'APP_KEY должен быть сгенерирован; значение не выводится.'),
            $this->check('Canonical origin', $productionOrigin, 'SEO_PUBLIC_URL должен быть фактическим HTTPS origin без path.'),
            $this->check('APP_URL alignment', $appHost === $originHost && str_starts_with((string) config('app.url'), 'https://'), 'APP_URL и canonical origin должны совпадать по HTTPS host.'),
            $this->pending('Search indexing', config('seo.indexing_enabled'), 'Открывать только после content/legal/infra smoke-check.'),
            $this->pending('MySQL', config('database.default') === 'mysql', 'Production и verification должны использовать MySQL 8+.'),
            $this->pending('Secure session cookie', (bool) config('session.secure'), 'SESSION_SECURE_COOKIE=true требуется на HTTPS.'),
            $this->pending('Encrypted session', (bool) config('session.encrypt'), 'SESSION_ENCRYPT=true рекомендуется для production.'),
            $this->pending('Queue', config('queue.default') !== 'sync', 'Нужен запущенный worker для выбранного async driver.'),
            $this->pending('Mail transport', ! in_array(config('mail.default'), ['log', 'array'], true), 'Реальный transport и получатель пока не подключены.'),
            $this->pending('Public storage link', is_link(public_path('storage')) || is_dir(public_path('storage')), 'Проверить storage:link и права каталога.'),
            [
                'check' => 'Map provider',
                'status' => 'DEFERRED',
                'note' => 'SSR Surroundings fallback остаётся рабочим; внешний provider не является условием запуска остальных страниц.',
            ],
        ];
    }

    private function check(string $name, bool $passed, string $note): array
    {
        return ['check' => $name, 'status' => $passed ? 'PASS' : 'BLOCKER', 'note' => $note];
    }

    private function pending(string $name, bool $passed, string $note): array
    {
        return ['check' => $name, 'status' => $passed ? 'PASS' : 'PENDING INFRASTRUCTURE', 'note' => $note];
    }
}

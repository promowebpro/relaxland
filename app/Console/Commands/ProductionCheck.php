<?php

namespace App\Console\Commands;

use App\Domain\Operations\ProductionReadiness;
use Illuminate\Console\Command;

class ProductionCheck extends Command
{
    protected $signature = 'app:production-check';

    protected $description = 'Validate production readiness without printing secrets or private data';

    public function handle(ProductionReadiness $readiness): int
    {
        $checks = $readiness->checks();

        $this->table(['Check', 'Status', 'Note'], collect($checks)->map(fn (array $check): array => [
            $check['check'],
            $check['status'],
            $check['note'],
        ]));

        return collect($checks)->contains(fn (array $check): bool => $check['status'] === 'BLOCKER')
            ? self::FAILURE
            : self::SUCCESS;
    }
}

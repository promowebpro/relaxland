<?php

namespace App\Domain\Genplan;

use Illuminate\Support\Collection;

final readonly class GenplanPageState
{
    public function __construct(
        public string $activeTab,
        public GenplanMode $mode,
        public ?Quarter $selectedQuarter,
        public ?InfrastructurePoint $selectedInfrastructure,
        public ?Plot $selectedPlot,
        public bool $incomplete,
    ) {}

    /**
     * @param  Collection<int, Quarter>  $quarters
     * @param  Collection<int, InfrastructurePoint>  $infrastructure
     */
    public static function resolve(
        ?string $view,
        ?string $mode,
        ?string $quarter,
        ?string $point,
        Collection $quarters,
        Collection $infrastructure,
    ): self {
        $activeTab = $view === 'surroundings' ? 'surroundings' : 'genplan';
        $activeMode = GenplanMode::tryFrom($mode ?? '') ?? GenplanMode::default();
        $selectedQuarter = null;
        $selectedInfrastructure = null;

        if ($activeTab === 'genplan') {
            $quarterCandidate = $quarter
                ? $quarters->first(fn (Quarter $record): bool => $record->slug === $quarter)
                : null;

            if ($quarterCandidate?->geometryFor($activeMode)) {
                $selectedQuarter = $quarterCandidate;
            }

            if (! $selectedQuarter && $point) {
                $pointCandidate = $infrastructure->first(
                    fn (InfrastructurePoint $record): bool => $record->slug === $point,
                );

                if ($pointCandidate && self::pointIsVisible($pointCandidate, $activeMode)) {
                    $selectedInfrastructure = $pointCandidate;
                }
            }
        }

        $hasGeometry = $quarters->contains(fn (Quarter $record): bool => (bool) $record->geometryFor($activeMode))
            || $infrastructure->contains(fn (InfrastructurePoint $record): bool => self::pointIsVisible($record, $activeMode));

        return new self(
            activeTab: $activeTab,
            mode: $activeMode,
            selectedQuarter: $selectedQuarter,
            selectedInfrastructure: $selectedInfrastructure,
            selectedPlot: null,
            incomplete: ! $hasGeometry,
        );
    }

    /** @param Collection<int, Plot> $plots */
    public function withSelectedPlot(?string $slug, Collection $plots): self
    {
        $selectedPlot = $this->selectedQuarter && $slug
            ? $plots->first(fn (Plot $plot): bool => $plot->slug === $slug)
            : null;

        return new self(
            activeTab: $this->activeTab,
            mode: $this->mode,
            selectedQuarter: $this->selectedQuarter,
            selectedInfrastructure: $this->selectedInfrastructure,
            selectedPlot: $selectedPlot,
            incomplete: $this->incomplete,
        );
    }

    private static function pointIsVisible(InfrastructurePoint $point, GenplanMode $mode): bool
    {
        $visible = $mode === GenplanMode::TwoD ? $point->show_on_2d : $point->show_on_3d;

        return $visible && (bool) $point->geometryFor($mode);
    }
}

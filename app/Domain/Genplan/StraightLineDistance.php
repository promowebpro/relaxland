<?php

namespace App\Domain\Genplan;

final class StraightLineDistance
{
    private const EARTH_RADIUS_KM = 6371.0088;

    public function kilometres(GeographicPoint $from, GeographicPoint $to): float
    {
        $latitudeFrom = deg2rad((float) $from->latitude);
        $latitudeTo = deg2rad((float) $to->latitude);
        $latitudeDelta = $latitudeTo - $latitudeFrom;
        $longitudeDelta = deg2rad((float) $to->longitude - (float) $from->longitude);

        $a = sin($latitudeDelta / 2) ** 2
            + cos($latitudeFrom) * cos($latitudeTo) * sin($longitudeDelta / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function label(GeographicPoint $from, GeographicPoint $to): string
    {
        $kilometres = $this->kilometres($from, $to);

        if ($kilometres < 1) {
            return (string) max(1, (int) round($kilometres * 1000)).' м по прямой';
        }

        return number_format($kilometres, 1, ',', ' ').' км по прямой';
    }
}

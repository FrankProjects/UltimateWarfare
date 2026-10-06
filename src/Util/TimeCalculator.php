<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

final class TimeCalculator
{
    public function calculateTimeLeft(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%s:%02d:%02d', $hours > 0 ? (string) $hours : '00', $minutes, $secs);
    }
}

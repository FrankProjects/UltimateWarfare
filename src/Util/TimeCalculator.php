<?php

namespace FrankProjects\UltimateWarfare\Util;

final class TimeCalculator
{
    /**
     * XXX TODO: Rewrite this function
     */
    public function calculateTimeLeft(int $seconds): string
    {
        $hms = "";
        $hours = floor($seconds / 3600);
        if ($hours > 0) {
            $hms .= $hours . ":";
        } else {
            $hms .= "00:";
        }

        $minutes = floor(($seconds / 60) % 60);
        if ($minutes > 0) {
            $hms .= str_pad((string)$minutes, 2, "0", STR_PAD_LEFT) . ":";
        } else {
            $hms .= "00:";
        }

        $seconds = floor($seconds % 60);

        $hms .= str_pad((string)$seconds, 2, "0", STR_PAD_LEFT) . "";

        return $hms;
    }
}

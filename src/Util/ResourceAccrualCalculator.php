<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources;

/**
 * Accrues a player's resources up to a given moment using the current income/upkeep rate.
 *
 * Gains are measured against a fixed anchor (the Unix epoch) instead of the last update, so rounding
 * telescopes: accruing once over a period yields exactly the same total as accruing many times within it.
 * Rounding loss is therefore bounded to less than one unit per resource per rate change.
 */
final class ResourceAccrualCalculator
{
    private const int SECONDS_PER_HOUR = 3600;

    public function accrue(Player $player, int $until): void
    {
        if ($until <= $player->getTimestampUpdate()) {
            return;
        }

        $player->setResources($this->project($player, $until));
        $player->setTimestampUpdate($until);
    }

    /**
     * The player's resources as they are at $at, without changing the player. Exact between state updates,
     * as the income/upkeep rate only changes when the player is synced.
     */
    public function project(Player $player, int $at): Resources
    {
        $from = $player->getTimestampUpdate();
        $resources = $player->getResources();
        if ($at <= $from) {
            return clone $resources;
        }

        $income = $player->getIncome();
        $upkeep = $player->getUpkeep();

        $projected = new Resources();
        $projected->setCash(
            $this->accrueValue($resources->getCash(), $income->getCash() - $upkeep->getCash(), $from, $at)
        );
        $projected->setFood(
            $this->accrueValue($resources->getFood(), $income->getFood() - $upkeep->getFood(), $from, $at)
        );
        $projected->setWood(
            $this->accrueValue($resources->getWood(), $income->getWood() - $upkeep->getWood(), $from, $at)
        );
        $projected->setSteel(
            $this->accrueValue($resources->getSteel(), $income->getSteel() - $upkeep->getSteel(), $from, $at)
        );

        return $projected;
    }

    private function accrueValue(int $value, int $ratePerHour, int $from, int $until): int
    {
        $gain = self::floorDiv($ratePerHour * $until, self::SECONDS_PER_HOUR)
            - self::floorDiv($ratePerHour * $from, self::SECONDS_PER_HOUR);

        return max(0, $value + $gain);
    }

    /**
     * Integer division rounding towards negative infinity (intdiv rounds towards zero)
     */
    private static function floorDiv(int $dividend, int $divisor): int
    {
        $quotient = intdiv($dividend, $divisor);
        if ($dividend % $divisor !== 0 && ($dividend < 0) !== ($divisor < 0)) {
            $quotient--;
        }

        return $quotient;
    }
}

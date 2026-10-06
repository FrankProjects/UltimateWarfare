<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;

class FleetUnit
{
    private int $id;
    private int $amount;
    private Fleet $fleet;
    private GameUnitEnum $gameUnit;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getFleet(): Fleet
    {
        return $this->fleet;
    }

    public function setFleet(Fleet $fleet): void
    {
        $this->fleet = $fleet;
    }

    public function getGameUnit(): GameUnitEnum
    {
        return $this->gameUnit;
    }

    public function setGameUnit(GameUnitEnum $gameUnit): void
    {
        $this->gameUnit = $gameUnit;
    }

    public static function createForFleet(Fleet $fleet, GameUnitEnum $gameUnit, int $amount): FleetUnit
    {
        $fleetUnit = new FleetUnit();
        $fleetUnit->setGameUnit($gameUnit);
        $fleetUnit->setAmount($amount);
        $fleetUnit->setFleet($fleet);

        return $fleetUnit;
    }
}

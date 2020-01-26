<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class FleetUnit
{
    private ?int $id;
    private int $amount;
    private Fleet $fleet;
    private GameUnit $gameUnit;

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

    public function setFleet(Fleet $fleet): void
    {
        $this->fleet = $fleet;
    }

    public function getGameUnit(): GameUnit
    {
        return $this->gameUnit;
    }

    public function setGameUnit(GameUnit $gameUnit): void
    {
        $this->gameUnit = $gameUnit;
    }

    public static function createForFleet(Fleet $fleet, GameUnit $gameUnit, int $amount): FleetUnit
    {
        $fleetUnit = new FleetUnit();
        $fleetUnit->setGameUnit($gameUnit);
        $fleetUnit->setAmount($amount);
        $fleetUnit->setFleet($fleet);

        return $fleetUnit;
    }
}

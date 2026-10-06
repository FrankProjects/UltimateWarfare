<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use FrankProjects\UltimateWarfare\Entity\BattleStats\AirBattleStats;
use FrankProjects\UltimateWarfare\Entity\BattleStats\GroundBattleStats;
use FrankProjects\UltimateWarfare\Entity\BattleStats\SeaBattleStats;

class BattleStats
{
    private int $health = 1;
    private int $armor = 1;
    private int $travelSpeed = 0;
    private AirBattleStats $airBattleStats;
    private SeaBattleStats $seaBattleStats;
    private GroundBattleStats $groundBattleStats;

    public function __construct(
        int $health = 1,
        int $armor = 1,
        int $travelSpeed = 0,
        AirBattleStats $airBattleStats = new AirBattleStats(),
        SeaBattleStats $seaBattleStats = new SeaBattleStats(),
        GroundBattleStats $groundBattleStats = new GroundBattleStats()
    ) {
        $this->health = $health;
        $this->armor = $armor;
        $this->travelSpeed = $travelSpeed;
        $this->airBattleStats = $airBattleStats;
        $this->seaBattleStats = $seaBattleStats;
        $this->groundBattleStats = $groundBattleStats;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function getTravelSpeed(): int
    {
        return $this->travelSpeed;
    }

    public function getAirBattleStats(): AirBattleStats
    {
        return $this->airBattleStats;
    }

    public function getSeaBattleStats(): SeaBattleStats
    {
        return $this->seaBattleStats;
    }

    public function getGroundBattleStats(): GroundBattleStats
    {
        return $this->groundBattleStats;
    }
}

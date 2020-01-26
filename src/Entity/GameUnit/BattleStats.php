<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\AirBattleStats;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\GroundBattleStats;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\SeaBattleStats;

class BattleStats
{
    private int $health = 1;
    private int $armor = 1;
    private int $travelSpeed = 0;
    private AirBattleStats $airBattleStats;
    private SeaBattleStats $seaBattleStats;
    private GroundBattleStats $groundBattleStats;

    public function __construct()
    {
        $this->airBattleStats = new AirBattleStats();
        $this->seaBattleStats = new SeaBattleStats();
        $this->groundBattleStats = new GroundBattleStats();
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

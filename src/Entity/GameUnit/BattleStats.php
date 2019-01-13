<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\AirBattleStats;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\GroundBattleStats;
use FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats\SeaBattleStats;

class BattleStats
{
    /**
     * @var int
     */
    private $health = 1;

    /**
     * @var int
     */
    private $armor = 1;

    /**
     * @var int
     */
    private $travelSpeed = 0;

    /**
     * @var AirBattleStats
     */
    private $airBattleStats;

    /**
     * @var SeaBattleStats
     */
    private $seaBattleStats;

    /**
     * @var GroundBattleStats
     */
    private $groundBattleStats;

    /**
     * BattleStats constructor.
     */
    public function __construct()
    {
        $this->airBattleStats = new AirBattleStats();
        $this->seaBattleStats = new SeaBattleStats();
        $this->groundBattleStats = new GroundBattleStats();
    }

    /**
     * Set health
     *
     * @param int $health
     */
    public function setHealth(int $health): void
    {
        $this->health = $health;
    }

    /**
     * Get health
     *
     * @return int
     */
    public function getHealth(): int
    {
        return $this->health;
    }

    /**
     * Set armor
     *
     * @param int $armor
     */
    public function setArmor(int $armor): void
    {
        $this->armor = $armor;
    }

    /**
     * Get armor
     *
     * @return int
     */
    public function getArmor(): int
    {
        return $this->armor;
    }

    /**
     * Set travelSpeed
     *
     * @param int $travelSpeed
     */
    public function setTravelSpeed(int $travelSpeed): void
    {
        $this->travelSpeed = $travelSpeed;
    }

    /**
     * Get travelSpeed
     *
     * @return int
     */
    public function getTravelSpeed(): int
    {
        return $this->travelSpeed;
    }

    /**
     * @return AirBattleStats
     */
    public function getAirBattleStats(): AirBattleStats
    {
        return $this->airBattleStats;
    }

    /**
     * @param AirBattleStats $airBattleStats
     */
    public function setAirBattleStats(AirBattleStats $airBattleStats): void
    {
        $this->airBattleStats = $airBattleStats;
    }

    /**
     * @return SeaBattleStats
     */
    public function getSeaBattleStats(): SeaBattleStats
    {
        return $this->seaBattleStats;
    }

    /**
     * @param SeaBattleStats $seaBattleStats
     */
    public function setSeaBattleStats(SeaBattleStats $seaBattleStats): void
    {
        $this->seaBattleStats = $seaBattleStats;
    }

    /**
     * @return GroundBattleStats
     */
    public function getGroundBattleStats(): GroundBattleStats
    {
        return $this->groundBattleStats;
    }

    /**
     * @param GroundBattleStats $groundBattleStats
     */
    public function setGroundBattleStats(GroundBattleStats $groundBattleStats): void
    {
        $this->groundBattleStats = $groundBattleStats;
    }
}

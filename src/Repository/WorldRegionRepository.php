<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldCountry;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldSector;

interface WorldRegionRepository
{
    /**
     * @param int $id
     * @return WorldRegion|null
     */
    public function find(int $id): ?WorldRegion;

    /**
     * @param WorldCountry $worldCountry
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldCountryAndPlayer(WorldCountry $worldCountry, ?Player $player): array;

    /**
     * @param WorldSector $worldSector
     * @param Player|null $player
     * @return WorldRegion[]
     */
    public function findByWorldSectorAndPlayer(WorldSector $worldSector, ?Player $player): array;

    /**
     * @param WorldRegion $worldRegion
     * @return array
     */
    public function getWorldGameUnitSumByWorldRegion(WorldRegion $worldRegion): array;

    /**
     * @param int $id
     * @param Player $player
     * @return WorldRegion|null
     */
    public function getPreviousWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    /**
     * @param int $id
     * @param Player $player
     * @return WorldRegion|null
     */
    public function getNextWorldRegionForPlayer(int $id, Player $player): ?WorldRegion;

    /**
     * @param WorldRegion $worldRegion
     */
    public function save(WorldRegion $worldRegion): void;
}

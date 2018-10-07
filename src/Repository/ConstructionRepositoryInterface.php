<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;

interface ConstructionRepositoryInterface
{
    /**
     * @param int $id
     * @return Construction|null
     */
    public function find(int $id): ?Construction;

    /**
     * @param Player $player
     * @return array
     */
    public function findByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return array
     */
    public function findByPlayerAndGameUnitType(Player $player, GameUnitType $gameUnitType): array;

    /**
     * @param int $timestamp
     * @return array
     */
    public function getCompletedConstructions(int $timestamp): array;

    /**
     * @param Construction $construction
     */
    public function remove(Construction $construction): void;

    /**
     * @param Construction $construction
     */
    public function save(Construction $construction): void;
}

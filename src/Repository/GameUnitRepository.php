<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameUnit;

interface GameUnitRepository
{
    /**
     * @param int $id
     * @return GameUnit|null
     */
    public function find(int $id): ?GameUnit;

    /**
     * @return GameUnit[]
     */
    public function findAll(): array;

    /**
     * @param GameUnit $gameUnit
     */
    public function save(GameUnit $gameUnit): void;
}

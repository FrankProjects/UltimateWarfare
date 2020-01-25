<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameUnit;

interface GameUnitRepository
{
    public function find(int $id): ?GameUnit;

    /**
     * @return GameUnit[]
     */
    public function findAll(): array;

    public function save(GameUnit $gameUnit): void;
}

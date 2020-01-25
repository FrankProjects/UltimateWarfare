<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameNews;

interface GameNewsRepository
{
    public function find(int $id): ?GameNews;

    /**
     * @return GameNews[]
     */
    public function findAll(): array;

    /**
     * @return GameNews[]
     */
    public function findActiveMainPageNews(): array;

    public function remove(GameNews $gameNews): void;

    public function save(GameNews $gameNews): void;
}

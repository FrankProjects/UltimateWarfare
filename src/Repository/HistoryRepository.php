<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\History;

interface HistoryRepository
{
    /**
     * @return History[]
     */
    public function findAll(): array;

    public function save(History $history): void;
}

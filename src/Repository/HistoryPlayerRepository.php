<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\HistoryPlayer;

interface HistoryPlayerRepository
{
    /**
     * @param int $worldId
     * @return HistoryPlayer[]
     */
    public function findByWorld(int $worldId): array;

    public function save(HistoryPlayer $historyPlayer): void;
}

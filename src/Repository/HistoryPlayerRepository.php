<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\HistoryPlayer;

interface HistoryPlayerRepository
{
    /**
     * @param int $worldId
     * @param int $round
     * @return HistoryPlayer[]
     */
    public function findByWorldAndRound(int $worldId, int $round): array;

    public function save(HistoryPlayer $historyPlayer): void;
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\HistoryFederation;

interface HistoryFederationRepository
{
    /**
     * @param int $worldId
     * @param int $round
     * @return HistoryFederation[]
     */
    public function findByWorldAndRound(int $worldId, int $round): array;

    public function save(HistoryFederation $historyFederation): void;
}

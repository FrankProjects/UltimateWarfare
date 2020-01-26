<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\HistoryFederation;

interface HistoryFederationRepository
{
    /**
     * @param int $worldId
     * @return HistoryFederation[]
     */
    public function findByWorld(int $worldId): array;

    public function save(HistoryFederation $historyFederation): void;
}

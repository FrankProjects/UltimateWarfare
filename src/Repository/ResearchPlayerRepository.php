<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;

interface ResearchPlayerRepository
{
    /**
     * @param int $timestamp
     * @return ResearchPlayer[]
     */
    public function getNonActiveCompletedResearch(int $timestamp): array;

    public function remove(ResearchPlayer $researchPlayer): void;

    public function save(ResearchPlayer $researchPlayer): void;
}

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

    /**
     * @param ResearchPlayer $researchPlayer
     */
    public function remove(ResearchPlayer $researchPlayer): void;

    /**
     * @param ResearchPlayer $researchPlayer
     */
    public function save(ResearchPlayer $researchPlayer): void;
}

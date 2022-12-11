<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;

interface ResearchPlayerRepository
{
    /**
     * @param int $timestamp
     * @return ResearchPlayer[]
     */
    public function getNonActiveCompletedResearch(int $timestamp): array;

    /**
     * @param Player $player
     * @return ResearchPlayer[]
     */
    public function findFinishedByPlayer(Player $player): array;

    public function remove(ResearchPlayer $researchPlayer): void;

    public function save(ResearchPlayer $researchPlayer): void;
}

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
     * @return ResearchPlayer[]
     */
    public function getAllNonActiveResearch(): array;

    /**
     * @param Player $player
     * @return ResearchPlayer[]
     */
    public function findFinishedByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @return ResearchPlayer[]
     */
    public function findOngoingByPlayer(Player $player): array;

    /**
     * Returns the highest completed level per research slug for a player.
     *
     * @return array<string, int>
     */
    public function getCompletedLevelsBySlug(Player $player): array;

    public function remove(ResearchPlayer $researchPlayer): void;

    public function save(ResearchPlayer $researchPlayer): void;
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Research;

interface ResearchRepository
{
    /**
     * @return Research[]
     */
    public function findAll(): array;

    /**
     * @param Player $player
     * @return Research[]
     */
    public function findOngoingByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @return Research[]
     */
    public function findFinishedByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @return Research[]
     */
    public function findUnresearchedByPlayer(Player $player): array;

    /**
     * @param Research $research
     */
    public function remove(Research $research): void;

    /**
     * @param Research $research
     */
    public function save(Research $research): void;
}

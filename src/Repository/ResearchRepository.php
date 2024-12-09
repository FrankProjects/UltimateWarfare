<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;

interface ResearchRepository
{
    public function find(int $id): ?Research;

    /**
     * @return Research[]
     */
    public function findAll(): array;

    /**
     * @param Player $player
     * @return ResearchPlayer[]
     */
    public function findOngoingByPlayer(Player $player): array;

    /**
     * @param Player $player
     * @return Research[]
     */
    public function findUnresearchedByPlayer(Player $player): array;

    public function remove(Research $research): void;

    public function save(Research $research): void;
}

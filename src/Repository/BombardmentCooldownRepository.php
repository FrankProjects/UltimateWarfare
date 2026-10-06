<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\BombardmentCooldown;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;

interface BombardmentCooldownRepository
{
    public function findActiveByWorldRegionAndOperation(
        WorldRegion $worldRegion,
        string $operationSlug
    ): ?BombardmentCooldown;

    /**
     * @return BombardmentCooldown[]
     */
    public function findActiveByPlayer(Player $player): array;

    public function save(BombardmentCooldown $bombardmentCooldown): void;

    public function removeExpired(): void;
}

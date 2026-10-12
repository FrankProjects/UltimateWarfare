<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\BombardmentCooldownRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use RuntimeException;

final class WorldActionService
{
    private WorldRepository $worldRepository;
    private PlayerRepository $playerRepository;
    private FederationRepository $federationRepository;
    private BombardmentCooldownRepository $bombardmentCooldownRepository;
    private WorldRegionRepository $worldRegionRepository;

    public function __construct(
        WorldRepository $worldRepository,
        PlayerRepository $playerRepository,
        FederationRepository $federationRepository,
        BombardmentCooldownRepository $bombardmentCooldownRepository,
        WorldRegionRepository $worldRegionRepository
    ) {
        $this->worldRepository = $worldRepository;
        $this->playerRepository = $playerRepository;
        $this->federationRepository = $federationRepository;
        $this->bombardmentCooldownRepository = $bombardmentCooldownRepository;
        $this->worldRegionRepository = $worldRegionRepository;
    }

    public function remove(int $worldId): void
    {
        $world = $this->getWorld($worldId);

        if (count($world->getPlayers()) > 0) {
            throw new RuntimeException("World has active players, can not remove!");
        }

        $this->reset($worldId);
        $this->worldRegionRepository->removeForWorld($world);
        $this->worldRepository->remove($world);
    }

    /**
     * @param int $worldId
     */
    public function reset(int $worldId): void
    {
        $world = $this->getWorld($worldId);

        foreach ($world->getFederations() as $federation) {
            $this->federationRepository->remove($federation);
        }

        foreach ($world->getPlayers() as $player) {
            $this->playerRepository->remove($player);
        }

        $this->bombardmentCooldownRepository->removeForWorld($world);
    }

    private function getWorld(int $worldId): World
    {
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            throw new RuntimeException("World does not exist!");
        }

        return $world;
    }
}

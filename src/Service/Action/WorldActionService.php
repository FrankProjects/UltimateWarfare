<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use RuntimeException;

final class WorldActionService
{
    /**
     * @var WorldRepository
     */
    private $worldRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var FederationRepository
     */
    private $federationRepository;

    /**
     * WorldActionService service
     *
     * @param WorldRepository $worldRepository
     * @param PlayerRepository $playerRepository
     * @param FederationRepository $federationRepository
     */
    public function __construct(
        WorldRepository $worldRepository,
        PlayerRepository $playerRepository,
        FederationRepository $federationRepository
    ) {
        $this->worldRepository = $worldRepository;
        $this->playerRepository = $playerRepository;
        $this->federationRepository = $federationRepository;
    }

    /**
     * @param int $worldId
     */
    public function remove(int $worldId): void
    {
        $world = $this->getWorld($worldId);

        if (count($world->getPlayers()) > 0) {
            throw new RuntimeException("World has active players, can not remove!");
        }

        $this->reset($worldId);
        $this->worldRepository->remove($world);
    }

    /**
     * @param int $worldId
     */
    public function reset(int $worldId): void
    {
        $world = $this->getWorld($worldId);

        foreach ($world->getPlayers() as $player) {
            $this->playerRepository->remove($player);
        }

        foreach ($world->getFederations() as $federation) {
            $this->federationRepository->remove($federation);
        }
    }

    /**
     * @param int $worldId
     * @return World
     */
    private function getWorld(int $worldId): World
    {
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            throw new RuntimeException("World does not exist!");
        }

        return $world;
    }
}

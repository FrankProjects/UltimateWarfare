<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use RuntimeException;

final class RegionActionService
{
    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var FederationRepository
     */
    private $federationRepository;

    /**
     * RegionActionService service
     *
     * @param WorldRegionRepository $worldRegionRepository
     * @param PlayerRepository $playerRepository
     * @param FederationRepository $federationRepository
     */
    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        PlayerRepository $playerRepository,
        FederationRepository $federationRepository)
    {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->playerRepository = $playerRepository;
        $this->federationRepository = $federationRepository;
    }

    /**
     * @param int $worldRegionId
     * @param Player $player
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws WorldRegionNotFoundException
     */
    public function buy(int $worldRegionId, Player $player): void
    {
        $worldRegion = $this->getWorldRegionByIdAndPlayer($worldRegionId, $player);

        if ($worldRegion->getPlayer() !== null) {
            throw new RunTimeException('Region is already owned by somebody!');
        }

        if ($player->getCash() < $player->getRegionPrice()) {
            throw new RunTimeException('You do not have enough money!');
        }

        $player->setCash($player->getCash() - $player->getRegionPrice());
        $player->setRegions($player->getRegions() + 1);

        // XXX TODO: Fix me
        //$player->setNetworth($this->calculateNetworth($player));

        $worldRegion->setPlayer($player);

        $federation = $player->getFederation();

        if ($federation != null) {
            $federation->setRegions($federation->getRegions() + 1);
            $federation->setNetworth($federation->getNetworth() + 1000);
            $this->federationRepository->save($federation);
        }

        $this->playerRepository->save($player);
        $this->worldRegionRepository->save($worldRegion);
    }

    /**
     * @param int $worldRegionId
     * @param Player $player
     * @return WorldRegion
     * @throws WorldRegionNotFoundException
     */
    public function getWorldRegionByIdAndPlayer(int $worldRegionId, Player $player): WorldRegion
    {
        $worldRegion = $this->worldRegionRepository->find($worldRegionId);

        if ($worldRegion === null) {
            throw new WorldRegionNotFoundException();
        }

        $sector = $worldRegion->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            throw new RunTimeException('World region is not part for your game world!');
        }


        return $worldRegion;
    }
}

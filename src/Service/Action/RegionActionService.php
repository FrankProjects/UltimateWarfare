<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use FrankProjects\UltimateWarfare\Util\TimeCalculator;
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
     * @var DistanceCalculator
     */
    private $distanceCalculator;

    /**
     * @var TimeCalculator
     */
    private $timeCalculator;

    /**
     * RegionActionService service
     *
     * @param WorldRegionRepository $worldRegionRepository
     * @param PlayerRepository $playerRepository
     * @param FederationRepository $federationRepository
     * @param DistanceCalculator $distanceCalculator
     * @param TimeCalculator $timeCalculator
     */
    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        PlayerRepository $playerRepository,
        FederationRepository $federationRepository,
        DistanceCalculator $distanceCalculator,
        TimeCalculator $timeCalculator
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->playerRepository = $playerRepository;
        $this->federationRepository = $federationRepository;
        $this->distanceCalculator = $distanceCalculator;
        $this->timeCalculator = $timeCalculator;
    }

    /**
     * @param WorldRegion $worldRegion
     * @param Player $player
     * @return array
     */
    public function getAttackFromWorldRegionList(WorldRegion $worldRegion, Player $player): array
    {
        if ($worldRegion->getPlayer() === null) {
            throw new RunTimeException('Can not attack region without owner!');
        }

        if ($worldRegion->getPlayer()->getId() == $player->getId()) {
            throw new RunTimeException('Can not attack your own region!');
        }

        $playerRegions = [];
        foreach ($player->getWorldRegions() as $playerWorldRegion) {
            $distance = $this->distanceCalculator->calculateDistance(
                    $playerWorldRegion->getX(),
                    $playerWorldRegion->getY(),
                    $worldRegion->getX(),
                    $worldRegion->getY()
                ) * 100;

            $playerWorldRegion->distance = $this->timeCalculator->calculateTimeLeft($distance);
            $playerRegions[] = $playerWorldRegion;
        }

        return $playerRegions;
    }

    /**
     * @param int $worldRegionId
     * @param Player $player
     * @throws WorldRegionNotFoundException
     */
    public function buyWorldRegion(int $worldRegionId, Player $player): void
    {
        $worldRegion = $this->getWorldRegionByIdAndPlayer($worldRegionId, $player);
        $resources = $player->getResources();

        if ($worldRegion->getPlayer() !== null) {
            throw new RunTimeException('Region is already owned by somebody!');
        }

        if ($resources->getCash() < $player->getRegionPrice()) {
            throw new RunTimeException('You do not have enough money!');
        }

        $resources->setCash($resources->getCash() - $player->getRegionPrice());

        $player->setResources($resources);
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

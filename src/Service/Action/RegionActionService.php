<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use FrankProjects\UltimateWarfare\Util\NetworthCalculator;
use FrankProjects\UltimateWarfare\Util\TimeCalculator;
use RuntimeException;

final class RegionActionService
{
    private WorldRegionRepository $worldRegionRepository;
    private PlayerRepository $playerRepository;
    private FederationRepository $federationRepository;
    private DistanceCalculator $distanceCalculator;
    private TimeCalculator $timeCalculator;

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
     * @return array<int<0, max>, array<string, WorldRegion|string>>
     */
    public function getAttackFromWorldRegionList(WorldRegion $worldRegion, Player $player): array
    {
        if ($worldRegion->getPlayer() === null) {
            throw new RuntimeException('Can not attack region without owner!');
        }

        if ($worldRegion->getPlayer()->getId() == $player->getId()) {
            throw new RuntimeException('Can not attack your own region!');
        }

        $playerRegions = [];
        foreach ($player->getWorldRegions() as $playerWorldRegion) {
            $travelTime = $this->distanceCalculator->calculateDistanceTravelTime(
                $playerWorldRegion->getX(),
                $playerWorldRegion->getY(),
                $worldRegion->getX(),
                $worldRegion->getY()
            );
            $travelTimeLeft = $this->timeCalculator->calculateTimeLeft($travelTime);
            $playerRegions[] = [
                'region' => $playerWorldRegion,
                'travelTime' => $travelTimeLeft
            ];
        }

        return $playerRegions;
    }

    /**
     * @return array<int<0, max>, array<string, WorldRegion|int>>
     */
    public function getOperationAttackFromWorldRegionList(WorldRegion $worldRegion, Player $player): array
    {
        if ($worldRegion->getPlayer() === null) {
            throw new RuntimeException('Can not attack region without owner!');
        }

        if ($worldRegion->getPlayer()->getId() == $player->getId()) {
            throw new RuntimeException('Can not attack your own region!');
        }

        $playerRegions = [];
        foreach ($player->getWorldRegions() as $playerWorldRegion) {
            $distance = $this->distanceCalculator->calculateDistance(
                $playerWorldRegion->getX(),
                $playerWorldRegion->getY(),
                $worldRegion->getX(),
                $worldRegion->getY()
            );
            $playerRegions[] = [
                'region' => $playerWorldRegion,
                'distance' => $distance
            ];
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
        $worldRegion = $this->getWorldRegionByIdAndWorld($worldRegionId, $player->getWorld());
        $resources = $player->getResources();

        if ($worldRegion->getPlayer() !== null) {
            throw new RuntimeException('Region is already owned by somebody!');
        }

        if ($resources->getCash() < $player->getRegionPrice()) {
            throw new RuntimeException('You do not have enough money!');
        }

        $resources->setCash($resources->getCash() - $player->getRegionPrice());

        $player->setResources($resources);
        $player->setNetworth($player->getNetworth() + NetworthCalculator::NETWORTH_CALCULATOR_REGION);

        $worldRegion->setPlayer($player);

        $federation = $player->getFederation();

        if ($federation != null) {
            $federation->setRegions($federation->getRegions() + 1);
            $federation->setNetworth($federation->getNetworth() + NetworthCalculator::NETWORTH_CALCULATOR_REGION);
            $this->federationRepository->save($federation);
        }

        $this->playerRepository->save($player);
        $this->worldRegionRepository->save($worldRegion);
    }

    /**
     * @param int $worldRegionId
     * @param World $world
     * @return WorldRegion
     * @throws WorldRegionNotFoundException
     */
    public function getWorldRegionByIdAndWorld(int $worldRegionId, World $world): WorldRegion
    {
        $worldRegion = $this->worldRegionRepository->find($worldRegionId);

        if ($worldRegion === null) {
            throw new WorldRegionNotFoundException();
        }

        if ($worldRegion->getWorld()->getId() != $world->getId()) {
            throw new RuntimeException('World region is not part for your game world!');
        }

        return $worldRegion;
    }

    /**
     * @param int $worldRegionId
     * @param Player $player
     * @return WorldRegion
     * @throws WorldRegionNotFoundException
     */
    public function getWorldRegionByIdAndPlayer(int $worldRegionId, Player $player): WorldRegion
    {
        $worldRegion = $this->getWorldRegionByIdAndWorld($worldRegionId, $player->getWorld());

        if ($worldRegion->getPlayer() === null) {
            throw new RuntimeException('World region has no owner!');
        }

        if ($worldRegion->getPlayer()->getId() != $player->getId()) {
            throw new RuntimeException('World region is not yours!');
        }


        return $worldRegion;
    }
}

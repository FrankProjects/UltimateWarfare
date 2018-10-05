<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Doctrine\ORM\ORMException;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Fleet;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use FrankProjects\UltimateWarfare\Util\TimeCalculator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegionController extends BaseGameController
{
    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * RegionController constructor.
     * @param WorldRegionRepository $worldRegionRepository
     */
    public function __construct(WorldRegionRepository $worldRegionRepository)
    {
        $this->worldRegionRepository = $worldRegionRepository;
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @return Response
     * @throws \Exception
     */
    public function attack(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        if ($region->getPlayer() === null) {
            $this->addFlash('error', "Can not attack nobody!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($region->getPlayer()->getId() == $player->getId()) {
            $this->addFlash('error', "Can not attack your own region!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        $distanceCalculator = new DistanceCalculator();
        $timeCalculator = new TimeCalculator();

        $playerRegions = [];
        foreach ($player->getWorldRegions() as $worldRegion) {
            $distance = $distanceCalculator->calculateDistance(
                    $worldRegion->getX(),
                    $worldRegion->getY(),
                    $region->getX(),
                    $region->getY()
                ) * 100;

            $worldRegion->distance = $timeCalculator->calculateTimeLeft($distance);
            $playerRegions[] = $worldRegion;
        }

        return $this->render('game/region/attackFrom.html.twig', [
            'region' => $region,
            'player' => $player,
            'mapUrl' => $this->getMapUrl(),
            'playerRegions' => $playerRegions
        ]);
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @param int $playerRegionId
     * @return Response
     * @throws \Exception
     */
    public function attackSelectGameUnits(Request $request, int $regionId, int $playerRegionId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        if ($region->getPlayer() === null) {
            $this->addFlash('error', "Can not attack nobody!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($region->getPlayer()->getId() == $player->getId()) {
            $this->addFlash('error', "Can not attack your own region!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        $playerRegion = $this->getWorldRegionByIdAndPlayer($playerRegionId, $player);

        if (!$playerRegion) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        if ($playerRegion->getPlayer() === null || $playerRegion->getPlayer()->getId() != $player->getId()) {
            $this->addFlash('error', "You are not owner of this region!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $playerRegion->getId()], 302);
        }

        $gameUnitType = $this->getEm()->getRepository('Game:GameUnitType')
            ->find(4);

        if ($request->getMethod() == 'POST') {
            $this->processSendGameUnits($request, $playerRegion, $region, $player, $gameUnitType);
            return $this->redirectToRoute('Game/Fleets', [], 302);
        }

        $playerRegion->gameUnits = $this->getRegionGameUnitData($playerRegion);

        return $this->render('game/region/attackSelectGameUnits.html.twig', [
            'region' => $region,
            'playerRegion' => $playerRegion,
            'player' => $player,
            'gameUnitType' => $gameUnitType,
        ]);
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @return Response
     * @throws \Exception
     */
    public function buy(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        if ($region->getPlayer() != null) {
            return $this->render('game/regionHasOwner.html.twig', [
                'player' => $player,
            ]);
        }

        $regionPrice = $player->getRegions() * 10000;

        if ($request->getMethod() == 'POST') {
            if ($player->getCash() < $regionPrice) {
                return $this->render('game/error/tooExpensive.html.twig', [
                    'player' => $player,
                ]);
            }

            $em = $this->getEm();
            $player->setCash($player->getCash() - $regionPrice);
            $player->setRegions($player->getRegions() + 1);
            $player->setNetworth($this->calculateNetworth($player));

            $region->setPlayer($player);

            $federation = $player->getFederation();

            if ($federation != null) {
                $federation->setRegions($federation->getRegions() + 1);
                $federation->setNetworth($federation->getNetworth() + 1000);
                $em->persist($federation);
            }

            $em->persist($player);
            $em->flush();

            $this->worldRegionRepository->save($region);

            $this->addFlash('success', 'You have bought a Region!');

            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }
        return $this->render('game/region/buy.html.twig', [
            'region' => $region,
            'player' => $player,
            'mapUrl' => $this->getMapUrl(),
            'price'  => $regionPrice
        ]);
    }

    /**
     * XXX TODO: Fix previous and next region navigation
     *
     * @param Request $request
     * @param int $regionId
     * @return Response
     */
    public function region(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        $region->gameUnits = $this->getRegionGameUnitData($region);
        return $this->render('game/region.html.twig', [
            'region' => $region,
            'player' => $player,
            'mapUrl' => $this->getMapUrl(),
            'previousRegionId' => 0,
            'nextRegionId' => 0,
        ]);
    }

    /**
     * XXX TODO: Add sorting support (by building space, population, buildings, units)
     *
     * @param Request $request
     * @return Response
     */
    public function regionList(Request $request): Response
    {
        $player = $this->getPlayer();
        $regions = $player->getWorldRegions();

        // XXX TODO: Make this more efficient...
        foreach ($regions as $region) {
            $buildingsInConstruction = 0;
            foreach ($region->getConstructions() as $regionConstruction) {
                if ($regionConstruction->getGameUnit()->getGameUnitType()->getId() == 1) {
                    $buildingsInConstruction += $regionConstruction->getNumber();
                }
            }
            $region->buildingsInConstruction = $buildingsInConstruction;

            $regionBuildings = 0;
            foreach ($region->getWorldRegionUnits() as $regionUnit) {
                if ($regionUnit->getGameUnit()->getGameUnitType()->getId() == 1) {
                    $regionBuildings += $regionUnit->getAmount();
                }
            }

            $region->buildings = $regionBuildings;
        }

        return $this->render('game/regionList.html.twig', [
            'regions' => $regions,
            'player' => $player,
            'mapUrl' => $this->getMapUrl(),
        ]);
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @param int $gameUnitTypeId
     * @return Response
     * @throws \Exception
     */
    public function removeGameUnits(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        $em = $this->getEm();
        $gameUnitType = $em->getRepository('Game:GameUnitType')
            ->find($gameUnitTypeId);

        if (!$gameUnitType) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($request->getMethod() == 'POST') {
            $this->processRemoveGameUnitsOrder($request, $region, $player, $gameUnitType);
        }

        $gameUnitTypes = $em->getRepository('Game:GameUnitType')
            ->findAll();

        $region->gameUnits = $this->getRegionGameUnitData($region);

        return $this->render('game/region/removeGameUnits.html.twig', [
            'region' => $region,
            'player' => $player,
            'gameUnitType' => $gameUnitType,
            'gameUnitTypes' => $gameUnitTypes,
        ]);
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @return Response
     * @throws \Exception
     */
    public function sendUnits(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        $gameUnitType = $this->getEm()->getRepository('Game:GameUnitType')
            ->find(4);

        if ($request->getMethod() == 'POST') {
            $targetRegionId = intval($request->request->get('target', 0));
            $targetRegion = $this->getWorldRegionByIdAndPlayer($targetRegionId, $player);

            if ($targetRegion) {
                $this->processSendGameUnits($request, $region, $targetRegion, $player, $gameUnitType);
            } else {
                $this->addFlash('error', "Target region does not exist.");
            }
        }

        $region->gameUnits = $this->getRegionGameUnitData($region);

        $distanceCalculator = new DistanceCalculator();

        $targetRegions = [];
        foreach ($player->getWorldRegions() as $worldRegion) {
            $distance = $distanceCalculator->calculateDistance(
                $worldRegion->getX(),
                $worldRegion->getY(),
                $region->getX(),
                $region->getY()
            ) * 100;

            $worldRegion->distance = $distance;
            $targetRegions[] = $worldRegion;
        }

        return $this->render('game/region/sendUnits.html.twig', [
            'region' => $region,
            'player' => $player,
            'gameUnitType' => $gameUnitType,
            'targetRegions' => $targetRegions
        ]);
    }

    /**
     * XXX TODO: Fix unit info page
     * XXX TODO: Fix buildtime to human readable format
     *
     * @param Request $request
     * @param int $regionId
     * @param int $gameUnitTypeId
     * @return Response
     * @throws \Exception
     */
    public function build(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
        $player = $this->getPlayer();
        $region = $this->getWorldRegionByIdAndPlayer($regionId, $player);

        if (!$region) {
            return $this->render('game/region/notFound.html.twig', [
                'player' => $player,
            ]);
        }

        $gameUnitType = $this->getEm()->getRepository('Game:GameUnitType')
            ->find($gameUnitTypeId);

        if (!$gameUnitType) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($request->getMethod() == 'POST') {
            $this->processBuildOrder($request, $region, $player, $gameUnitType);
        }

        $gameUnitTypes = $this->getEm()->getRepository('Game:GameUnitType')
            ->findAll();

        $region->gameUnits = $this->getRegionGameUnitData($region);
        $region->construction = $this->getWorldRegionConstructionData($region);

        $spaceLeft = 0;
        if ($gameUnitType->getId() == 1) {
            $buildingsInConstruction = 0;
            foreach ($region->getConstructions() as $regionConstruction) {
                if ($regionConstruction->getGameUnit()->getGameUnitType()->getId() == 1) {
                    $buildingsInConstruction += $regionConstruction->getNumber();
                }
            }

            $regionBuildings = 0;
            foreach ($region->getWorldRegionUnits() as $regionUnit) {
                if ($regionUnit->getGameUnit()->getGameUnitType()->getId() == 1) {
                    $regionBuildings += $regionUnit->getAmount();
                }
            }

            $spaceLeft = $region->getSpace() - $regionBuildings - $buildingsInConstruction;
        }

        return $this->render('game/region/build.html.twig', [
            'region' => $region,
            'player' => $player,
            'spaceLeft' => $spaceLeft,
            'gameUnitType' => $gameUnitType,
            'gameUnitTypes' => $gameUnitTypes,
        ]);
    }

    /**
     * @return string
     */
    private function getMapUrl(): string
    {
        $user = $this->getGameUser();
        return $user->getMapDesign()->getUrl();
    }

    /**
     * @param Player $player
     * @return int
     */
    private function calculateNetworth(Player $player): int
    {
        // XXX TODO
        return 1000;
    }

    /**
     * @param WorldRegion $worldRegion
     * @return array
     */
    private function getRegionGameUnitData(WorldRegion $worldRegion): array
    {
        $gameUnitData = $this->getGameUnitFields();

        foreach ($worldRegion->getWorldRegionUnits() as $data) {
            $gameUnitData[$data->getGameUnit()->getRowName()] += $data->getAmount();
        }

        return $gameUnitData;
    }

    /**
     * @param WorldRegion $worldRegion
     * @return array
     */
    private function getWorldRegionConstructionData(WorldRegion $worldRegion): array
    {
        $gameUnitData = $this->getGameUnitFields();

        try {
            // Refresh object to always get latest construction queue
            $this->getEm()->refresh($worldRegion);
        } catch (ORMException $e) {
        }

        foreach ($worldRegion->getConstructions() as $data) {
            $gameUnitData[$data->getGameUnit()->getRowName()] += $data->getNumber();
        }

        return $gameUnitData;
    }

    /**
     * @return array
     */
    private function getGameUnitFields(): array
    {
        $em = $this->getEm();
        $repository = $em->getRepository('Game:GameUnit');
        $gameUnits = $repository->findAll();
        $gameUnitArray = [];
        foreach ($gameUnits as $unit) {
            $gameUnitArray[$unit->getRowName()] = 0;
        }

        return $gameUnitArray;
    }

    /**
     * @param Request $request
     * @param WorldRegion $region
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return bool
     * @throws \Exception
     */
    private function processBuildOrder(Request $request, WorldRegion $region, Player $player, GameUnitType $gameUnitType): bool
    {
        $priceCash = 0;
        $priceWood = 0;
        $priceSteel = 0;
        $totalBuild = 0;
        $constructions = [];

        foreach ($gameUnitType->getGameUnits() as $gameUnit) {
            if ($request->request->has($gameUnit->getId())) {
                $amount = $request->request->get($gameUnit->getId(), 0);
                if ($amount == 0) {
                    continue;
                }

                if ($amount < 0) {
                    $this->addFlash('error', "You can't build negative " . $gameUnit->getName() . "s!");
                    return false;
                }

                $priceCash = $priceCash + ($amount * $gameUnit->getPriceCash());
                $priceWood = $priceWood + ($amount * $gameUnit->getPriceWood());
                $priceSteel = $priceSteel + ($amount * $gameUnit->getPriceSteel());

                if ($gameUnitType->getId() == 1) {
                    $totalBuild = $totalBuild + $amount;
                }
            }
        }

        if ($gameUnitType->getId() == 1) {
            $buildingsInConstruction = 0;
            foreach ($region->getConstructions() as $regionConstruction) {
                if ($regionConstruction->getGameUnit()->getGameUnitType()->getId() == 1) {
                    $buildingsInConstruction += $regionConstruction->getNumber();
                }
            }

            $regionBuildings = 0;
            foreach ($region->getWorldRegionUnits() as $regionUnit) {
                if ($regionUnit->getGameUnit()->getGameUnitType()->getId() == 1) {
                    $regionBuildings += $regionUnit->getAmount();
                }
            }

            $totalSpace = $region->getSpace() - $regionBuildings - $buildingsInConstruction;

            if ($totalBuild > $totalSpace) {
                $this->addFlash('error', 'You dont have that much buildingspace.');
                return false;
            }
        }

        if ($priceCash > $player->getCash()) {
            $this->addFlash('error', "You don't have enough cash to build that.");
            return false;
        }
        if ($priceWood > $player->getWood()) {
            $this->addFlash('error', "You don't have enough wood to build that.");
            return false;
        }
        if ($priceSteel > $player->getSteel()) {
            $this->addFlash('error', "You don't have enough steel to build that.");
            return false;
        }

        $player->setCash($player->getCash() - $priceCash);
        $player->setWood($player->getWood() - $priceWood);
        $player->setSteel($player->getSteel() - $priceSteel);


        foreach ($gameUnitType->getGameUnits() as $gameUnit) {
            if ($request->request->has($gameUnit->getId())) {
                $amount = $request->request->get($gameUnit->getId(), 0);
                if ($amount > 0) {
                    $constructions[] = Construction::create($region, $player, $gameUnit, $amount);

                    if ($gameUnitType->getId() == 4) {
                        $this->addFlash('success', $amount . " " . $gameUnit->getName() . "s are now being trained.");
                    } else {
                        $this->addFlash('success', $amount . " " . $gameUnit->getName() . "s are now being built.");
                    }
                }
            }
        }

        $em = $this->getEm();
        $em->persist($player);

        foreach ($constructions as $construction) {
            $em->persist($construction);
        }

        $em->flush();

        return true;
    }

    /**
     * @param Request $request
     * @param WorldRegion $region
     * @param WorldRegion $targetRegion
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return bool
     * @throws \Exception
     */
    private function processSendGameUnits(Request $request, WorldRegion $region, WorldRegion $targetRegion, Player $player, GameUnitType $gameUnitType): bool
    {
        $em = $this->getEm();
        $sector = $targetRegion->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            $this->addFlash('error', "Target region does not exist.");
            return false;
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            $this->addFlash('error', "Region is not owned by you.");
            return false;
        }

        $distanceCalculator = new DistanceCalculator();
        $distance = $distanceCalculator->calculateDistance($targetRegion->getX(), $targetRegion->getY(), $region->getX(), $region->getY());

        $fleet = new Fleet();
        $fleet->setPlayer($player);
        $fleet->setWorldRegion($region);
        $fleet->setTargetWorldRegion($targetRegion);
        $fleet->setTimestamp(time());
        $fleet->setTimestampArrive(time() + ($distance * 100));

        foreach ($gameUnitType->getGameUnits() as $gameUnit) {
            if ($request->request->has($gameUnit->getId())) {
                $amount = intval($request->request->get($gameUnit->getId(), 0));
                if ($amount == 0) {
                    continue;
                }

                if ($amount < 0) {
                    $this->addFlash('error', "You can't send negative " . $gameUnit->getName() . "s!");
                    return false;
                }

                $hasUnit = false;
                foreach ($region->getWorldRegionUnits() as $regionUnit) {
                    if ($regionUnit->getGameUnit()->getId() == $gameUnit->getId()) {
                        $hasUnit = true;
                        /** @var \FrankProjects\UltimateWarfare\Entity\WorldRegionUnit $regionUnit */
                        if ($amount > $regionUnit->getAmount()) {
                            $this->addFlash('error', "You don't have that many " . $gameUnit->getName() . "s!");
                            return false;
                        }

                        $regionUnit->setAmount($regionUnit->getAmount() - $amount);

                        $fleetUnit = new FleetUnit();
                        $fleetUnit->setGameUnit($regionUnit->getGameUnit());
                        $fleetUnit->setAmount($amount);
                        $fleetUnit->setFleet($fleet);

                        $em->persist($fleetUnit);
                        $em->persist($regionUnit);
                    }
                }

                if ($hasUnit !== true) {
                    $this->addFlash('error', "You don't have that many " . $gameUnit->getName() . "s!");
                    return false;
                }

                $this->addFlash('success', "You have sent your Units!");
            }
        }

        $em->persist($fleet);
        $em->persist($player);
        $em->flush();

        return true;
    }

    /**
     * @param Request $request
     * @param WorldRegion $region
     * @param Player $player
     * @param GameUnitType $gameUnitType
     * @return bool
     * @throws \Exception
     */
    private function processRemoveGameUnitsOrder(Request $request, WorldRegion $region, Player $player, GameUnitType $gameUnitType): bool
    {
        $em = $this->getEm();
        $networth = 0;

        foreach ($gameUnitType->getGameUnits() as $gameUnit) {
            if ($request->request->has($gameUnit->getId())) {
                $amount = $request->request->get($gameUnit->getId(), 0);
                if ($amount == 0) {
                    continue;
                }

                if ($amount < 0) {
                    $this->addFlash('error', "You can't destroy negative " . $gameUnit->getName() . "s!");
                    return false;
                }

                $hasUnit = false;
                foreach ($region->getWorldRegionUnits() as $regionUnit) {
                    if ($regionUnit->getGameUnit()->getId() == $gameUnit->getId()) {
                        $hasUnit = true;
                        /** @var \FrankProjects\UltimateWarfare\Entity\WorldRegionUnit $regionUnit */
                        if ($amount > $regionUnit->getAmount()) {
                            $this->addFlash('error', "You don't have that many " . $gameUnit->getName() . "s!");
                            return false;
                        }

                        $regionUnit->setAmount($regionUnit->getAmount() - $amount);
                        $em->persist($regionUnit);
                    }
                }

                if ($hasUnit !== true) {
                    $this->addFlash('error', "You don't have that many " . $gameUnit->getName() . "s!");
                    return false;
                }

                $networth += $amount * $gameUnit->getNetworth();

                if ($gameUnitType->getId() == 4) {
                    $this->addFlash('success', "You have disbanded {$amount} {$gameUnit->getName()}'s");
                } else {
                    $this->addFlash('success', "You have destroyed {$amount} {$gameUnit->getName()}'s");
                }
            }
        }

        $player->setNetworth($player->getNetworth() - $networth);

        if ($player->getFederation() !== null) {
            $federation = $player->getFederation();
            $federation->setNetworth($federation->getNetworth() - $networth);
            $em->persist($federation);
        }

        $em->persist($player);
        $em->flush();

        return true;
    }

    /**
     * @param int $worldRegionId
     * @param Player $player
     * @return WorldRegion|null
     */
    private function getWorldRegionByIdAndPlayer(int $worldRegionId, Player $player): ?WorldRegion
    {
        $worldRegion = $this->worldRegionRepository->find($worldRegionId);
        if (!$worldRegion) {
            return null;
        }

        $sector = $worldRegion->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            return null;
        }

        return $worldRegion;
    }
}

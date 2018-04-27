<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Doctrine\ORM\ORMException;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegionController extends BaseGameController
{
    /**
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @param int $regionId
     * @return Response
     */
    public function attack(Request $request, int $regionId): Response
    {
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
        $em = $this->getEm();
        $region = $em->getRepository('Game:WorldRegion')
            -> findOneBy(['id' => $regionId]);

        if (!$region) {
            return $this->render('game/regionNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $sector = $region->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            return $this->render('game/regionNotFound.html.twig', [
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

            $player->setCash($player->getCash() - $regionPrice);
            $player->setRegions($player->getRegions() + 1);
            $player->setNetworth($this->calculateNetworth($player));

            $region->setPlayer($player);

            $federation = $player->getFederation();

            if($federation != null){
                $federation->setRegions($federation->getRegions() + 1);
                $federation->setNetworth($federation->getNetworth() + 1000);
                $em->persist($federation);
            }

            $em->persist($player);
            $em->persist($region);
            $em->flush();

            $this->addFlash('success', 'You have bought a Region!');

            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }
        return $this->render('game/buyRegion.html.twig', [
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
        $em = $this->getEm();
        $region = $em->getRepository('Game:WorldRegion')
            -> findOneBy(['id' => $regionId]);

        if(!$region) {
            return $this->render('game/regionNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $sector = $region->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            return $this->render('game/regionNotFound.html.twig', [
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
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @param int $regionId
     * @param int $gameUnitTypeId
     * @return Response
     */
    public function remove(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
    }

    /**
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @param int $regionId
     * @return Response
     */
    public function sendUnits(Request $request, int $regionId): Response
    {
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
        $em = $this->getEm();

        $region = $em->getRepository('Game:WorldRegion')
            ->find($regionId);

        if (!$region) {
            return $this->render('game/regionNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $gameUnitType = $em->getRepository('Game:GameUnitType')
            ->find($gameUnitTypeId);

        if (!$gameUnitType) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        $sector = $region->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            return $this->render('game/regionNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        if ($region->getPlayer()->getId() != $player->getId()) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $region->getId()], 302);
        }

        if ($request->getMethod() == 'POST') {
            $this->processBuildOrder($request, $region, $player, $gameUnitType);
        }

        $gameUnitTypes = $em->getRepository('Game:GameUnitType')
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

        return $this->render('game/build.html.twig', [
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

        foreach($gameUnitType->getGameUnits() as $gameUnit) {
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

                if ($gameUnitType->getId() == 1){
                    $totalBuild = $totalBuild + $amount;
                }
            }
        }

        if($gameUnitType->getId() == 1){
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

        if($priceCash > $player->getCash()){
            $this->addFlash('error', "You don't have enough cash to build that.");
            return false;
        }
        if($priceWood > $player->getWood()){
            $this->addFlash('error', "You don't have enough wood to build that.");
            return false;
        }
        if($priceSteel > $player->getSteel()){
            $this->addFlash('error', "You don't have enough steel to build that.");
            return false;
        }

        $player->setCash($player->getCash() - $priceCash);
        $player->setWood($player->getWood() - $priceWood);
        $player->setSteel($player->getSteel() - $priceSteel);


        foreach($gameUnitType->getGameUnits() as $gameUnit) {
            if ($request->request->has($gameUnit->getId())) {
                $amount = $request->request->get($gameUnit->getId(), 0);
                if($amount > 0){
                    $constructions[] = Construction::create($region, $player, $gameUnit, $amount);

                    if($gameUnitType->getId() == 4){
                        $this->addFlash('success', $amount . " " . $gameUnit->getName() . "s are now being trained.");
                    }else{
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
}

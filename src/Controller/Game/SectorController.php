<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldCountry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SectorController extends BaseGameController
{
    /**
     * @param Request $request
     * @param int $sectorId
     * @return Response
     */
    public function sector(Request $request, int $sectorId): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $sector = $em->getRepository('Game:WorldSector')
            -> findOneBy(['id' => $sectorId, 'world' => $player->getWorld()]);

        if(!$sector) {
            return $this->render('game/sectorNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $countries = [];
        foreach($sector->getWorldCountries() as $country) {
            $country->regionCount = $this->getRegionCount($country, $player);
            $countries[$country->getCX()][$country->getCY()] = $country;
        }

        return $this->render('game/sector.html.twig', [
            'sector' => $sector,
            'countries' => $countries,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => true,
                'searchFree' => false,
                'searchPlayerName' => false,
                'mapUrl' => $this->getMapUrl()
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param int $sectorId
     * @return Response
     */
    public function searchFree(Request $request, int $sectorId): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $sector = $em->getRepository('Game:WorldSector')
            -> findOneBy(['id' => $sectorId, 'world' => $player->getWorld()]);

        if(!$sector) {
            return $this->render('game/sectorNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $countries = [];
        foreach($sector->getWorldCountries() as $country) {
            $country->regionCount = $this->getRegionCount($country);
            $countries[$country->getCX()][$country->getCY()] = $country;
        }

        return $this->render('game/sector.html.twig', [
            'sector' => $sector,
            'countries' => $countries,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => true,
                'searchFree' => true,
                'searchPlayerName' => false,
                'mapUrl' => $this->getMapUrl()
            ]
        ]);
    }

    /**
     * @param Request $request
     * @param int $sectorId
     * @return Response
     */
    public function searchPlayer(Request $request, int $sectorId): Response
    {
        $playerName = $request->request->get('playerName');
        $player = $this->getPlayer();
        $em = $this->getEm();
        $sector = $em->getRepository('Game:WorldSector')
            -> findOneBy(['id' => $sectorId, 'world' => $player->getWorld()]);

        if(!$sector) {
            return $this->render('game/sectorNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $playerSearch = $em->getRepository('Game:Player')
            ->findBy(['name' => $playerName, 'world' => $player->getWorld()]);

        if ($playerSearch) {
            $playerSearch = $playerSearch[0];
            $searchFound = true;
        } else {
            $searchFound = false;
        }

        $countries = [];
        foreach($sector->getWorldCountries() as $country) {
            if ($searchFound) {
                $country->regionCount = $this->getRegionCount($country, $playerSearch);
            } else {
                $country->regionCount = 0;
            }

            $countries[$country->getCX()][$country->getCY()] = $country;
        }

        return $this->render('game/sector.html.twig', [
            'sector' => $sector,
            'countries' => $countries,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => $searchFound,
                'searchFree' => false,
                'searchPlayerName' => true,
                'playerName' => $playerName,
                'mapUrl' => $this->getMapUrl()
            ]
        ]);
    }

    /**
     * @param WorldCountry $country
     * @param Player $player
     * @return int
     */
    private function getRegionCount(WorldCountry $country, $player = null): int
    {
        $em = $this->getEm();
        $regionCount = $em->getRepository('Game:WorldRegion')
            ->findBy([
                'worldCountry' => $country,
                'player' => $player
            ]);

        return count($regionCount);
    }

    /**
     * @return string
     */
    private function getMapUrl(): string
    {
        $gameAccount = $this->getGameAccount();
        return $gameAccount->getMapDesign()->getUrl();
    }
}

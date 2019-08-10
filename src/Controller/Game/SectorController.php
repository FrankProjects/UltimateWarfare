<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldCountry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;
use Symfony\Component\HttpFoundation\Response;

final class SectorController extends BaseGameController
{
    /**
     * @var WorldSectorRepository
     */
    private $worldSectorRepository;

    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * SectorController
     *
     * @param WorldSectorRepository $worldSectorRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        WorldSectorRepository $worldSectorRepository,
        WorldRegionRepository $worldRegionRepository,
        PlayerRepository $playerRepository
    ) {
        $this->worldSectorRepository = $worldSectorRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param int $sectorId
     * @return Response
     */
    public function sector(int $sectorId): Response
    {
        $player = $this->getPlayer();
        $sector = $this->worldSectorRepository->findByIdAndWorld($sectorId, $player->getWorld());

        if (!$sector) {
            return $this->render('game/sectorNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $countries = [];
        foreach ($sector->getWorldCountries() as $country) {
            $country->regionCount = $this->getRegionCount($country, $player);
            $countries[$country->getX()][$country->getY()] = $country;
        }

        return $this->render('game/sector.html.twig', [
            'sector' => $sector,
            'countries' => $countries,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => true,
                'searchFree' => false,
                'searchPlayerName' => false
            ]
        ]);
    }

    /**
     * @param int $sectorId
     * @return Response
     */
    public function searchFree(int $sectorId): Response
    {
        $player = $this->getPlayer();
        $sector = $this->worldSectorRepository->findByIdAndWorld($sectorId, $player->getWorld());

        if (!$sector) {
            return $this->render('game/sectorNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $countries = [];
        foreach ($sector->getWorldCountries() as $country) {
            $country->regionCount = $this->getRegionCount($country);
            $countries[$country->getX()][$country->getY()] = $country;
        }

        return $this->render('game/sector.html.twig', [
            'sector' => $sector,
            'countries' => $countries,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => true,
                'searchFree' => true,
                'searchPlayerName' => false
            ]
        ]);
    }

    /**
     * @param int $sectorId
     * @param string $playerName
     * @return Response
     */
    public function searchPlayer(int $sectorId, string $playerName): Response
    {
        $player = $this->getPlayer();

        $sector = $this->worldSectorRepository->findByIdAndWorld($sectorId, $player->getWorld());

        if (!$sector) {
            return $this->render('game/sectorNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $playerSearch = $this->playerRepository->findByNameAndWorld($playerName, $player->getWorld());

        if ($playerSearch) {
            $searchFound = true;
        } else {
            $searchFound = false;
        }

        $countries = [];
        foreach ($sector->getWorldCountries() as $country) {
            if ($searchFound) {
                $country->regionCount = $this->getRegionCount($country, $playerSearch);
            } else {
                $country->regionCount = 0;
            }

            $countries[$country->getX()][$country->getY()] = $country;
        }

        return $this->render('game/sector.html.twig', [
            'sector' => $sector,
            'countries' => $countries,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => $searchFound,
                'searchFree' => false,
                'searchPlayerName' => true,
                'playerName' => $playerName
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
        $worldRegions = $this->worldRegionRepository->findByWorldCountryAndPlayer($country, $player);
        return count($worldRegions);
    }
}

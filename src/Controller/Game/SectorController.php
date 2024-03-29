<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;
use Symfony\Component\HttpFoundation\Response;

final class SectorController extends BaseGameController
{
    private WorldSectorRepository $worldSectorRepository;

    public function __construct(
        WorldSectorRepository $worldSectorRepository,
    ) {
        $this->worldSectorRepository = $worldSectorRepository;
    }

    public function sector(int $sectorId): Response
    {
        $player = $this->getPlayer();
        $sector = $this->worldSectorRepository->findByIdAndWorld($sectorId, $player->getWorld());

        if ($sector === null) {
            return $this->render(
                'game/sectorNotFound.html.twig',
                [
                    'player' => $player,
                ]
            );
        }

        $regions = [];
        foreach ($sector->getWorldRegions() as $region) {
            $regions[$region->getY()][$region->getX()] = $region;
        }

        return $this->render(
            'game/sector.html.twig',
            [
                'sector' => $sector,
                'regions' => $regions,
                'player' => $player,
                'mapSettings' => [
                    'searchFound' => true,
                    'searchFree' => false,
                    'searchPlayerName' => false
                ]
            ]
        );
    }
}

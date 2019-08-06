<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\WorldCountryRepository;
use Symfony\Component\HttpFoundation\Response;

final class CountryController extends BaseGameController
{
    /**
     * @param int $countryId
     * @param WorldCountryRepository $worldCountryRepository
     * @return Response
     */
    public function country(int $countryId, WorldCountryRepository $worldCountryRepository): Response
    {
        $player = $this->getPlayer();
        $country = $worldCountryRepository->find($countryId);

        if (!$country) {
            return $this->render('game/countryNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $sector = $country->getWorldSector();

        if ($sector->getWorld()->getId() != $player->getWorld()->getId()) {
            return $this->render('game/countryNotFound.html.twig', [
                'player' => $player,
            ]);
        }

        $regions = [];
        foreach ($country->getWorldRegions() as $region) {
            $regions[$region->getY()][$region->getX()] = $region;
        }

        return $this->render('game/country.html.twig', [
            'sector' => $sector,
            'country' => $country,
            'regions' => $regions,
            'player' => $player,
            'mapSettings' => [
                'searchFound' => true,
                'searchFree' => false,
                'searchPlayerName' => false
            ]
        ]);
    }
}

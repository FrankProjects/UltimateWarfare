<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CountryController extends BaseGameController
{
    /**
     * @param Request $request
     * @param int $countryId
     * @return Response
     */
    public function country(Request $request, int $countryId): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $country = $em->getRepository('Game:WorldCountry')
            -> findOneBy(['id' => $countryId]);

        if(!$country) {
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

        #$worldRegions = $em->getRepository('Game:WorldRegion')
        #    ->findBy(['worldId' => $player->getWorldId(), 'countryId' => $countryId]);

        $regions = [];
        foreach($country->getWorldRegions() as $region) {
            $regions[$region->getRX()][$region->getRY()] = $region;
        }

        return $this->render('game/country.html.twig', [
            'sector' => $sector,
            'country' => $country,
            'regions' => $regions,
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
     * @return string
     */
    private function getMapUrl(): string
    {
        $gameAccount = $this->getGameAccount();
        return $gameAccount->getMapDesign()->getUrl();
    }
}

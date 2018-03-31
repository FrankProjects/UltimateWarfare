<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class WorldController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function selectWorld(Request $request): Response
    {
        $em = $this->getEm();
        $worlds = $em->getRepository('Game:World')
            ->findByPublic(1);

        return $this->render('game/selectWorld.html.twig', [
            'worlds' => $worlds,
            'user' => $this->getGameUser()
        ]);
    }

    /**
     * @param Request $request
     * @param int $worldId
     * @return Response
     */
    public function selectName(Request $request, int $worldId): Response
    {
        $em = $this->getEm();
        $world = $em->getRepository('Game:World')
            ->find($worldId);

        return $this->render('game/selectName.html.twig', [
            'world' => $world,
            'user' => $this->getGameUser()
        ]);
    }

    /**
     * @param Request $request
     * @param int $worldId
     * @return Response
     * @throws Exception
     */
    public function start(Request $request, int $worldId): Response
    {
        $name = $request->request->get('name', null);

        $user = $this->getGameUser();
        $em = $this->getEm();
        $world = $em->getRepository('Game:World')
            ->find($worldId);

        $players = $em->getRepository('Game:Player')
            ->findByUser($user);
        foreach($players as $player) {
            if ($player->getWorld()->getId() == $worldId) {
                throw new AccessDeniedException("Player already in this world!");
            }
        }

        $player = Player::create($user, $name, $world);
        $em->persist($player);
        $em->flush();

        return $this->redirectToRoute('Game/Login', array(), 302);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function world(Request $request): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();

        $sectors = [];
        foreach($world->getWorldSectors() as $sector) {
            $sector->regionCount = $this->getRegionCount($sector, $player);
            $sectors[$sector->getX()][$sector->getY()] = $sector;
        }

        return $this->render('game/world.html.twig', [
            'sectors' => $sectors,
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
     * @return Response
     */
    public function searchFree(Request $request): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();

        $sectors = [];
        foreach($world->getWorldSectors() as $sector) {
            $sector->regionCount = $this->getRegionCount($sector);
            $sectors[$sector->getX()][$sector->getY()] = $sector;
        }

        return $this->render('game/world.html.twig', [
            'sectors' => $sectors,
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
     * @return Response
     */
    public function searchPlayer(Request $request): Response
    {
        $playerName = $request->request->get('playerName');
        $player = $this->getPlayer();
        $em = $this->getEm();
        $world = $player->getWorld();

        $playerSearch = $em->getRepository('Game:Player')
            ->findBy(['name' => $playerName, 'world' => $player->getWorld()]);

        if ($playerSearch) {
            $playerSearch = $playerSearch[0];
            $searchFound = true;
        } else {
            $searchFound = false;
        }

        $sectors = [];
        foreach($world->getWorldSectors() as $sector) {
            if ($searchFound) {
                $sector->regionCount = $this->getRegionCount($sector, $playerSearch);
            } else {
                $sector->regionCount = 0;
            }
            $sectors[$sector->getX()][$sector->getY()] = $sector;
        }

        return $this->render('game/world.html.twig', [
            'sectors' => $sectors,
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
     * @param WorldSector $sector
     * @param Player $player
     * @return int
     */
    private function getRegionCount(WorldSector $sector, $player = null): int
    {
        $em = $this->getEm();
        $regionCount = $em->getRepository('Game:WorldRegion')
            ->findBy([
                'worldSector' => $sector,
                'player' => $player
            ]);

        return count($regionCount);
    }

    /**
     * @return string
     */
    private function getMapUrl(): string
    {
        $user = $this->getGameUser();
        return $user->getMapDesign()->getUrl();
    }
}

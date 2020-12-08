<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\WorldGeneratorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WorldController extends BaseGameController
{
    private PlayerRepository $playerRepository;
    private WorldRepository $worldRepository;
    private WorldRegionRepository $worldRegionRepository;

    public function __construct(
        PlayerRepository $playerRepository,
        WorldRepository $worldRepository,
        WorldRegionRepository $worldRegionRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->worldRepository = $worldRepository;
        $this->worldRegionRepository = $worldRegionRepository;
    }

    public function create(WorldGeneratorService $worldGeneratorService): Response
    {
        $worlds = $this->worldRepository->findByPublic(true);

        if (count($worlds) !== 0) {
            $this->addFlash('error', 'There are active worlds, no need to create a new one at this moment');
            return $this->redirectToRoute('Game/SelectWorld', [], 302);
        }

        $this->addFlash('success', 'Successfully created a new world!');
        $worldGeneratorService->generateBasicWorld();

        return $this->redirectToRoute('Game/SelectWorld', [], 302);
    }

    public function selectWorld(): Response
    {
        $validWorlds = [];
        $worlds = $this->worldRepository->findByPublic(true);
        foreach ($worlds as $world) {
            if ($world->isJoinableForUser($this->getGameUser())) {
                $validWorlds[] = $world;
            }
        }

        return $this->render(
            'game/selectWorld.html.twig',
            [
                'worlds' => $validWorlds,
                'user' => $this->getGameUser()
            ]
        );
    }

    public function selectName(int $worldId): Response
    {
        $world = $this->worldRepository->find($worldId);

        return $this->render(
            'game/selectName.html.twig',
            [
                'world' => $world,
                'user' => $this->getGameUser()
            ]
        );
    }

    public function start(Request $request, int $worldId): Response
    {
        $name = $request->request->get('name', null);

        $user = $this->getGameUser();
        $world = $this->worldRepository->find($worldId);

        foreach ($user->getPlayers() as $player) {
            if ($player->getWorld()->getId() == $worldId) {
                $this->addFlash('error', 'You are already playing in this world!');
                return $this->redirectToRoute('Game/SelectName', ['worldId' => $worldId], 302);
            }
        }

        if ($this->playerRepository->findByNameAndWorld($name, $world) !== null) {
            $this->addFlash('error', 'Another player with this name already exist!');
            return $this->redirectToRoute('Game/SelectName', ['worldId' => $worldId], 302);
        }

        $player = Player::create($user, $name, $world);
        $this->playerRepository->save($player);
        return $this->redirectToRoute('Game/Login', [], 302);
    }

    public function world(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();

        $sectors = [];
        foreach ($world->getWorldSectors() as $sector) {
            $sector->regionCount = $this->getRegionCount($sector, $player);
            $sectors[$sector->getX()][$sector->getY()] = $sector;
        }

        return $this->render(
            'game/world.html.twig',
            [
                'sectors' => $sectors,
                'player' => $player,
                'mapSettings' => [
                    'searchFound' => true,
                    'searchFree' => false,
                    'searchPlayerName' => false
                ]
            ]
        );
    }

    public function searchFree(): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();

        $sectors = [];
        foreach ($world->getWorldSectors() as $sector) {
            $sector->regionCount = $this->getRegionCount($sector);
            $sectors[$sector->getX()][$sector->getY()] = $sector;
        }

        return $this->render(
            'game/world.html.twig',
            [
                'sectors' => $sectors,
                'player' => $player,
                'mapSettings' => [
                    'searchFound' => true,
                    'searchFree' => true,
                    'searchPlayerName' => false
                ]
            ]
        );
    }

    public function searchPlayer(Request $request): Response
    {
        $playerName = $request->request->get('playerName');
        $player = $this->getPlayer();
        $world = $player->getWorld();

        $playerSearch = $this->playerRepository->findByNameAndWorld($playerName, $world);

        if ($playerSearch) {
            $searchFound = true;
        } else {
            $searchFound = false;
        }

        $sectors = [];
        foreach ($world->getWorldSectors() as $sector) {
            if ($searchFound) {
                $sector->regionCount = $this->getRegionCount($sector, $playerSearch);
            } else {
                $sector->regionCount = 0;
            }
            $sectors[$sector->getX()][$sector->getY()] = $sector;
        }

        return $this->render(
            'game/world.html.twig',
            [
                'sectors' => $sectors,
                'player' => $player,
                'mapSettings' => [
                    'searchFound' => $searchFound,
                    'searchFree' => false,
                    'searchPlayerName' => true,
                    'playerName' => $playerName
                ]
            ]
        );
    }

    private function getRegionCount(WorldSector $sector, ?Player $player = null): int
    {
        $worldRegions = $this->worldRegionRepository->findByWorldSectorAndPlayer($sector, $player);
        return count($worldRegions);
    }
}

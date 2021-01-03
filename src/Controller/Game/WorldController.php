<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        return $this->render('game/world.html.twig', [
            'player' => $player
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTiles(Request $request): JsonResponse
    {
        $player = $this->getPlayer();

        $json = $request->get('json_request');
        $params = json_decode($json, true);
        $tiles = [];
        foreach ($params['coords'] as $coord) {
            $worldRegion = $this->worldRegionRepository->findByWorldXY($player->getWorld(), intval($coord['x']), intval($coord['y']));
            if ($worldRegion !== null) {
                $tiles[] = $worldRegion->toArray();
            }
        }
        //dump($tiles);
        //exit();
        return $this->json($tiles);
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WorldController extends BaseGameController
{
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var WorldRepository
     */
    private $worldRepository;

    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * WorldController constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param WorldRepository $worldRepository
     * @param WorldRegionRepository $worldRegionRepository
     */
    public function __construct(
        PlayerRepository $playerRepository,
        WorldRepository $worldRepository,
        WorldRegionRepository $worldRegionRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->worldRepository = $worldRepository;
        $this->worldRegionRepository = $worldRegionRepository;
    }

    /**
     * @return Response
     */
    public function selectWorld(): Response
    {
        $worlds = $this->worldRepository->findByPublic(true);

        return $this->render('game/selectWorld.html.twig', [
            'worlds' => $worlds,
            'user' => $this->getGameUser()
        ]);
    }

    /**
     * @param int $worldId
     * @return Response
     */
    public function selectName(int $worldId): Response
    {
        $world = $this->worldRepository->find($worldId);

        return $this->render('game/selectName.html.twig', [
            'world' => $world,
            'user' => $this->getGameUser()
        ]);
    }

    /**
     * @param Request $request
     * @param int $worldId
     * @return Response
     */
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

    /**
     * @return Response
     */
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

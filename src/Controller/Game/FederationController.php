<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Service\Action\FederationActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FederationController extends BaseGameController
{
    private FederationRepository $federationRepository;
    private FederationNewsRepository $federationNewsRepository;
    private FederationActionService $federationActionService;

    public function __construct(
        FederationRepository $federationRepository,
        FederationNewsRepository $federationNewsRepository,
        FederationActionService $federationActionService
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationNewsRepository = $federationNewsRepository;
        $this->federationActionService = $federationActionService;
    }

    public function federation(): Response
    {
        $player = $this->getPlayer();
        if ($player->getFederation() === null) {
            return $this->render(
                'game/federation/noFederation.html.twig',
                [
                    'player' => $this->getPlayer()
                ]
            );
        }

        return $this->render(
            'game/federation/yourFederation.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }

    public function showFederation(int $federationId): Response
    {
        $federation = $this->federationRepository->findByIdAndWorld($federationId, $this->getPlayer()->getWorld());
        if ($federation === null) {
            return $this->render(
                'game/federation/noFederation.html.twig',
                [
                    'player' => $this->getPlayer()
                ]
            );
        }

        return $this->render(
            'game/federation/federation.html.twig',
            [
                'player' => $this->getPlayer(),
                'federation' => $federation
            ]
        );
    }

    public function federationNews(): Response
    {
        $player = $this->getPlayer();
        if ($player->getFederation() === null) {
            return $this->render(
                'game/federation/noFederation.html.twig',
                [
                    'player' => $this->getPlayer()
                ]
            );
        }

        $federationNews = $this->federationNewsRepository->findByFederationSortedByTimestamp($player->getFederation());

        return $this->render(
            'game/federation/news.html.twig',
            [
                'player' => $player,
                'federationNews' => $federationNews
            ]
        );
    }

    public function createFederation(Request $request): Response
    {
        $federationName = trim((string) $request->get('name'));

        try {
            if ($request->isMethod(Request::METHOD_POST)) {
                $this->federationActionService->createFederation($this->getPlayer(), $federationName);
                $this->addFlash('success', "You successfully created a new Federation");

                return $this->redirectToRoute('Game/Federation');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/create.html.twig',
            [
                'player' => $this->getPlayer(),
                'federationName' => $federationName
            ]
        );
    }

    public function joinFederation(): Response
    {
        $federations = $this->federationRepository->findByWorldSortedByRegion($this->getPlayer()->getWorld());

        return $this->render(
            'game/federation/join.html.twig',
            [
                'player' => $this->getPlayer(),
                'federations' => $federations
            ]
        );
    }

    public function listFederations(): Response
    {
        $federations = $this->federationRepository->findByWorldSortedByRegion($this->getPlayer()->getWorld());

        return $this->render(
            'game/federation/list.html.twig',
            [
                'player' => $this->getPlayer(),
                'federations' => $federations
            ]
        );
    }

    public function sendAid(Request $request): Response
    {
        try {
            if ($request->isMethod(Request::METHOD_POST) && $request->get('player') !== null) {
                $aidPlayerId = intval($request->get('player'));
                $this->federationActionService->sendAid($this->getPlayer(), $aidPlayerId, $request->get('resources'));
                $this->addFlash('success', "You have send aid!");

                return $this->redirectToRoute('Game/Federation');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/sendAid.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }

    public function removeFederation(Request $request): Response
    {
        try {
            if ($request->isMethod(Request::METHOD_POST)) {
                $this->federationActionService->removeFederation($this->getPlayer());
                $this->addFlash('success', "You successfully removed a Federation");

                return $this->redirectToRoute('Game/Federation');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/removeFederation.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }

    public function changeFederationName(Request $request): Response
    {
        try {
            if ($request->isMethod(Request::METHOD_POST)) {
                $this->federationActionService->changeFederationName($this->getPlayer(), $request->get('name'));
                $this->addFlash('success', "You successfully changed the Federation name!");

                return $this->redirectToRoute('Game/Federation');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/changeFederationName.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }

    public function leaveFederation(Request $request): Response
    {
        try {
            if ($request->isMethod(Request::METHOD_POST)) {
                $this->federationActionService->leaveFederation($this->getPlayer());
                $this->addFlash('success', "You successfully left your Federation");

                return $this->redirectToRoute('Game/Federation');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/leaveFederation.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }

    public function kickPlayer(int $playerId): Response
    {
        try {
            $this->federationActionService->kickPlayer($this->getPlayer(), $playerId);
            $this->addFlash('success', "You successfully kicked a player");
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Federation');
    }

    public function updateLeadershipMessage(Request $request): Response
    {
        try {
            if ($request->isMethod(Request::METHOD_POST) && $request->get('message') !== null) {
                $this->federationActionService->updateLeadershipMessage($this->getPlayer(), $request->get('message'));
                $this->addFlash('success', "You successfully updated the leadership message");

                return $this->redirectToRoute('Game/Federation');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/updateLeadershipMessage.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }

    public function changePlayerHierarchy(Request $request): Response
    {
        try {
            if (
                $request->isMethod(Request::METHOD_POST) &&
                $request->get('playerId') !== null &&
                $request->get('role') !== null
            ) {
                $this->federationActionService->changePlayerHierarchy(
                    $this->getPlayer(),
                    $request->get('playerId'),
                    $request->get('role')
                );
                $this->addFlash('success', "You successfully updated a player rank");
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/changePlayerHierarchy.html.twig',
            [
                'player' => $this->getPlayer()
            ]
        );
    }
}

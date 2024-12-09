<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Service\Action\FederationApplicationActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FederationApplicationController extends BaseGameController
{
    private FederationRepository $federationRepository;
    private FederationApplicationActionService $federationApplicationActionService;

    public function __construct(
        FederationRepository $federationRepository,
        FederationApplicationActionService $federationApplicationActionService
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationApplicationActionService = $federationApplicationActionService;
    }

    public function showFederationApplications(): Response
    {
        return $this->render(
            'game/federation/applications.html.twig',
            [
                'player' => $this->getPlayer(),
            ]
        );
    }

    public function acceptFederationApplication(int $federationApplicationId): Response
    {
        try {
            $this->federationApplicationActionService->acceptFederationApplication(
                $this->getPlayer(),
                $federationApplicationId
            );
            $this->addFlash('success', 'You successfully accepted a new player!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Federation/Applications');
    }

    public function rejectFederationApplication(int $federationApplicationId): Response
    {
        try {
            $this->federationApplicationActionService->rejectFederationApplication(
                $this->getPlayer(),
                $federationApplicationId
            );
            $this->addFlash('success', 'You successfully rejected a player!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Federation/Applications');
    }

    public function sendApplication(Request $request, int $federationId): Response
    {
        $player = $this->getPlayer();
        $federation = $this->federationRepository->findByIdAndWorld($federationId, $player->getWorld());
        try {
            $application = $request->request->getString('application');

            if (
                $federation !== null &&
                $request->isMethod(Request::METHOD_POST) &&
                $application !== ''
            ) {
                $this->federationApplicationActionService->sendFederationApplication(
                    $this->getPlayer(),
                    $federation,
                    $application
                );
                $this->addFlash('success', 'You successfully send your application!');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/sendApplication.html.twig',
            [
                'player' => $this->getPlayer(),
            ]
        );
    }
}

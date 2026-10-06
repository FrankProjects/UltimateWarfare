<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Service\Action\FederationApplicationActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function applicationsApi(): JsonResponse
    {
        $player = $this->getPlayer();

        if ($player->getFederation() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Not in a federation']);
        }

        $applications = [];
        foreach ($player->getFederation()->getFederationApplications() as $application) {
            $applications[] = [
                'id' => $application->getId(),
                'playerName' => $application->getPlayer()->getName(),
                'application' => $application->getApplication(),
            ];
        }

        return new JsonResponse(['success' => true, 'applications' => $applications]);
    }

    public function acceptApi(int $federationApplicationId): JsonResponse
    {
        try {
            $this->federationApplicationActionService->acceptFederationApplication(
                $this->getPlayer(),
                $federationApplicationId
            );

            return new JsonResponse(['success' => true, 'message' => 'Application accepted']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function rejectApi(int $federationApplicationId): JsonResponse
    {
        try {
            $this->federationApplicationActionService->rejectFederationApplication(
                $this->getPlayer(),
                $federationApplicationId
            );

            return new JsonResponse(['success' => true, 'message' => 'Application rejected']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function sendApplicationApi(Request $request, int $federationId): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $federation = $this->federationRepository->findByIdAndWorld($federationId, $player->getWorld());
            if ($federation === null) {
                return new JsonResponse(['success' => false, 'message' => 'Federation not found']);
            }

            /** @var array{application?: string} $data */
            $data = json_decode($request->getContent(), true);
            $application = trim($data['application'] ?? '');
            if ($application === '') {
                return new JsonResponse(['success' => false, 'message' => 'Application text is required']);
            }

            $this->federationApplicationActionService->sendFederationApplication(
                $player,
                $federation,
                $application
            );

            return new JsonResponse(['success' => true, 'message' => 'Application sent successfully']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

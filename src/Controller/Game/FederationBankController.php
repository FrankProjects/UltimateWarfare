<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Service\Action\FederationBankActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

final class FederationBankController extends BaseGameController
{
    private FederationBankActionService $federationBankActionService;

    public function __construct(
        FederationBankActionService $federationBankActionService
    ) {
        $this->federationBankActionService = $federationBankActionService;
    }

    public function depositApi(Request $request): JsonResponse
    {
        try {
            /** @var array{resources?: array<string, string>} $data */
            $data = json_decode($request->getContent(), true);
            /** @var array<string, string> $resources */
            $resources = $data['resources'] ?? [];
            $this->federationBankActionService->deposit($this->getPlayer(), $resources);

            return new JsonResponse(['success' => true, 'message' => 'Deposit successful']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function withdrawApi(Request $request): JsonResponse
    {
        try {
            /** @var array{resources?: array<string, string>} $data */
            $data = json_decode($request->getContent(), true);
            /** @var array<string, string> $resources */
            $resources = $data['resources'] ?? [];
            $this->federationBankActionService->withdraw($this->getPlayer(), $resources);

            return new JsonResponse(['success' => true, 'message' => 'Withdrawal successful']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

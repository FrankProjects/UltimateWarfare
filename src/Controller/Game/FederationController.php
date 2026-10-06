<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Service\Action\FederationActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    public function statusApi(): JsonResponse
    {
        $player = $this->getPlayer();

        if (!$player->getWorld()->getFederation()) {
            return new JsonResponse([
                'success' => true,
                'hasFederation' => false,
                'federationEnabled' => false,
            ]);
        }

        $federation = $player->getFederation();
        if ($federation === null) {
            return new JsonResponse([
                'success' => true,
                'hasFederation' => false,
                'federationEnabled' => true,
            ]);
        }

        $members = [];
        foreach ($federation->getPlayers() as $federationPlayer) {
            $members[] = [
                'id' => $federationPlayer->getId(),
                'name' => $federationPlayer->getName(),
                'hierarchy' => $federationPlayer->getFederationHierarchy(),
                'regions' => count($federationPlayer->getWorldRegions()),
                'netWorth' => $federationPlayer->getNetWorth(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'hasFederation' => true,
            'federationEnabled' => true,
            'playerId' => $player->getId(),
            'hierarchy' => $player->getFederationHierarchy(),
            'federation' => [
                'id' => $federation->getId(),
                'name' => $federation->getName(),
                'leaderMessage' => $federation->getLeaderMessage(),
                'regions' => $federation->getRegions(),
                'netWorth' => $federation->getNetWorth(),
                'bank' => [
                    'cash' => $federation->getResources()->getCash(),
                    'wood' => $federation->getResources()->getWood(),
                    'steel' => $federation->getResources()->getSteel(),
                    'food' => $federation->getResources()->getFood(),
                ],
                'members' => $members,
            ],
            'playerResources' => [
                'cash' => $player->getResources()->getCash(),
                'wood' => $player->getResources()->getWood(),
                'steel' => $player->getResources()->getSteel(),
                'food' => $player->getResources()->getFood(),
            ],
        ]);
    }

    public function showApi(int $federationId): JsonResponse
    {
        $player = $this->getPlayer();
        $federation = $this->federationRepository->findByIdAndWorld($federationId, $player->getWorld());

        if ($federation === null) {
            return new JsonResponse(['success' => false, 'message' => 'Federation not found']);
        }

        $members = [];
        foreach ($federation->getPlayers() as $federationPlayer) {
            $members[] = [
                'name' => $federationPlayer->getName(),
                'hierarchy' => $federationPlayer->getFederationHierarchy(),
                'regions' => count($federationPlayer->getWorldRegions()),
                'netWorth' => $federationPlayer->getNetWorth(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'federation' => [
                'id' => $federation->getId(),
                'name' => $federation->getName(),
                'regions' => $federation->getRegions(),
                'netWorth' => $federation->getNetWorth(),
            ],
            'members' => $members,
        ]);
    }

    public function newsApi(): JsonResponse
    {
        $player = $this->getPlayer();

        if ($player->getFederation() === null) {
            return new JsonResponse(['success' => false, 'message' => 'Not in a federation']);
        }

        $newsItems = $this->federationNewsRepository->findByFederationSortedByTimestamp(
            $player->getFederation()
        );

        $news = [];
        foreach ($newsItems as $item) {
            $news[] = [
                'timestamp' => $item->getTimestamp(),
                'news' => $item->getNews(),
            ];
        }

        return new JsonResponse(['success' => true, 'news' => $news]);
    }

    public function listApi(): JsonResponse
    {
        $player = $this->getPlayer();

        if (!$player->getWorld()->getFederation()) {
            return new JsonResponse(['success' => false, 'message' => 'Federations not enabled']);
        }

        $federations = $this->federationRepository->findByWorldSortedByRegion($player->getWorld());

        $items = [];
        foreach ($federations as $federation) {
            $items[] = [
                'id' => $federation->getId(),
                'name' => $federation->getName(),
                'founder' => $federation->getFounder()->getName(),
                'players' => count($federation->getPlayers()),
                'regions' => $federation->getRegions(),
                'netWorth' => $federation->getNetWorth(),
            ];
        }

        return new JsonResponse([
            'success' => true,
            'federations' => $items,
            'hasFederation' => $player->getFederation() !== null,
        ]);
    }

    public function createApi(Request $request): JsonResponse
    {
        try {
            /** @var array{name?: string} $data */
            $data = json_decode($request->getContent(), true);
            $name = trim($data['name'] ?? '');
            $this->federationActionService->createFederation($this->getPlayer(), $name);

            return new JsonResponse(['success' => true, 'message' => 'Federation created successfully']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function sendAidApi(Request $request): JsonResponse
    {
        try {
            /** @var array{playerId?: int, resources?: array<string, string>} $data */
            $data = json_decode($request->getContent(), true);
            $playerId = $data['playerId'] ?? 0;
            /** @var array<string, string> $resources */
            $resources = $data['resources'] ?? [];
            $this->federationActionService->sendAid($this->getPlayer(), $playerId, $resources);

            return new JsonResponse(['success' => true, 'message' => 'Aid sent successfully']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function removeApi(): JsonResponse
    {
        try {
            $this->federationActionService->removeFederation($this->getPlayer());

            return new JsonResponse(['success' => true, 'message' => 'Federation removed']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function changeNameApi(Request $request): JsonResponse
    {
        try {
            /** @var array{name?: string} $data */
            $data = json_decode($request->getContent(), true);
            $name = trim($data['name'] ?? '');
            $this->federationActionService->changeFederationName($this->getPlayer(), $name);

            return new JsonResponse(['success' => true, 'message' => 'Federation name changed']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function leaveApi(): JsonResponse
    {
        try {
            $this->federationActionService->leaveFederation($this->getPlayer());

            return new JsonResponse(['success' => true, 'message' => 'You have left the federation']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function kickPlayerApi(int $playerId): JsonResponse
    {
        try {
            $this->federationActionService->kickPlayer($this->getPlayer(), $playerId);

            return new JsonResponse(['success' => true, 'message' => 'Player kicked']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateMessageApi(Request $request): JsonResponse
    {
        try {
            /** @var array{message?: string} $data */
            $data = json_decode($request->getContent(), true);
            $message = $data['message'] ?? '';
            $this->federationActionService->updateLeadershipMessage($this->getPlayer(), $message);

            return new JsonResponse(['success' => true, 'message' => 'Leadership message updated']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function changeRoleApi(Request $request): JsonResponse
    {
        try {
            /** @var array{playerId?: int, role?: int} $data */
            $data = json_decode($request->getContent(), true);
            $playerId = $data['playerId'] ?? 0;
            $role = $data['role'] ?? 0;
            $this->federationActionService->changePlayerHierarchy($this->getPlayer(), $playerId, $role);

            return new JsonResponse(['success' => true, 'message' => 'Player rank updated']);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

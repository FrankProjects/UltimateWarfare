<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Repository\BattleSummaryRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\BattleEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

final class BattleController extends BaseGameController
{
    private BattleEngine $battleEngine;
    private FleetRepository $fleetRepository;
    private BattleSummaryRepository $battleSummaryRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private RegionActionService $regionActionService;

    public function __construct(
        BattleEngine $battleEngine,
        FleetRepository $fleetRepository,
        BattleSummaryRepository $battleSummaryRepository,
        GameUnitRegistry $gameUnitRegistry,
        RegionActionService $regionActionService
    ) {
        $this->battleEngine = $battleEngine;
        $this->fleetRepository = $fleetRepository;
        $this->battleSummaryRepository = $battleSummaryRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->regionActionService = $regionActionService;
    }

    public function battleApi(int $fleetId): JsonResponse
    {
        $player = $this->getPlayer();
        $fleet = $this->fleetRepository->findByIdAndPlayer($fleetId, $player);

        if ($fleet === null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Fleet does not exist'
            ], 404);
        }

        try {
            $targetRegion = $fleet->getTargetWorldRegion();
            $battleResults = $this->battleEngine->battle($fleet);
            $hasWon = $battleResults->hasWon();

            // Region and fleet data for updating the world map without a reload, like after buying a region
            $fleetUnits = [];
            if ($hasWon) {
                $regionUnits = $this->gameUnitRegistry->getRegionUnitSummary($targetRegion);
                $newlyVisibleEnemyUnits = $this->regionActionService->getNeighborEnemyUnitPresence(
                    $targetRegion,
                    $player
                );
            } else {
                $regionUnits = $this->gameUnitRegistry->getMaskedRegionUnitSummary($targetRegion);
                $newlyVisibleEnemyUnits = [];

                // Survivors of a lost battle stay in the fleet at the target; without survivors the fleet is gone
                foreach ($fleet->getFleetUnits() as $fleetUnit) {
                    $fleetUnits[] = [
                        'name' => $this->gameUnitRegistry->find($fleetUnit->getGameUnit())->getName(),
                        'amount' => $fleetUnit->getAmount(),
                    ];
                }
            }

            return new JsonResponse([
                'success' => true,
                'hasWon' => $hasWon,
                'battleLog' => $battleResults->getBattleLog(),
                'lostUnits' => $this->formatLostUnits($battleResults->getLostUnits()),
                'message' => $hasWon ? 'Victory! You won the battle!' : 'Defeat! You lost the battle.',
                'regionId' => $targetRegion->getId(),
                'regionUnits' => $regionUnits,
                'newlyVisibleEnemyUnits' => $newlyVisibleEnemyUnits,
                // A lost battle can leave a water region without defenders, which releases it
                'regionHasOwner' => $targetRegion->getPlayer() !== null,
                'fleetUnits' => $fleetUnits,
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function battleSummaryApi(int $battleSummaryId): JsonResponse
    {
        $player = $this->getPlayer();
        $battleSummary = $this->battleSummaryRepository->find($battleSummaryId);

        if (
            $battleSummary === null ||
            $battleSummary->getWorld()->getId() !== $player->getWorld()->getId() ||
            !$battleSummary->isParticipant($player)
        ) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Battle summary does not exist'
            ], 404);
        }

        $isAttacker = $battleSummary->getAttacker()->getId() === $player->getId();

        return new JsonResponse([
            'success' => true,
            'battleSummary' => [
                'id' => $battleSummary->getId(),
                'attacker' => $battleSummary->getAttacker()->getName(),
                'defender' => $battleSummary->getDefender()->getName(),
                'attackerWon' => $battleSummary->hasAttackerWon(),
                'hasWon' => $isAttacker === $battleSummary->hasAttackerWon(),
                'date' => date('M d, Y H:i', $battleSummary->getTimestamp()),
                'battleLog' => $battleSummary->getBattleLogLines(),
                'lostUnits' => $this->formatLostUnits($battleSummary->getLostUnits()),
            ],
        ]);
    }

    /**
     * Battles from before losses were recorded have no lost units
     *
     * @param array<string, array<int, int>>|null $lostUnits
     *
     * @return array<string, array<int, array{name: string, amount?: int, level?: int}>>|null
     */
    private function formatLostUnits(?array $lostUnits): ?array
    {
        if ($lostUnits === null) {
            return null;
        }

        $formatted = [];
        foreach (['attacker', 'defender'] as $side) {
            $formatted[$side] = [];
            foreach ($lostUnits[$side] ?? [] as $gameUnitValue => $amount) {
                $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitValue);
                if ($gameUnitEnum === null) {
                    continue;
                }

                $gameUnit = $this->gameUnitRegistry->find($gameUnitEnum);
                $formatted[$side][] = [
                    'name' => $amount === 1 ? $gameUnit->getName() : $gameUnit->getNameMulti(),
                    'amount' => $amount,
                ];
            }
        }

        $formatted['defenderBuildings'] = [];
        foreach ($lostUnits['defenderBuildings'] ?? [] as $gameUnitValue => $level) {
            $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitValue);
            if ($gameUnitEnum !== null) {
                $formatted['defenderBuildings'][] = [
                    'name' => $this->gameUnitRegistry->find($gameUnitEnum)->getName(),
                    'level' => $level,
                ];
            }
        }

        return $formatted;
    }
}

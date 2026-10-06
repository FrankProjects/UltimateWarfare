<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\BombardmentCooldownRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\FleetRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\PlayerSetupService;
use FrankProjects\UltimateWarfare\Service\WorldGeneratorService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WorldController extends BaseGameController
{
    private PlayerRepository $playerRepository;
    private WorldRepository $worldRepository;
    private FleetRepository $fleetRepository;
    private BombardmentCooldownRepository $bombardmentCooldownRepository;
    private GameUnitRegistry $gameUnitRegistry;
    private WorldRegionRepository $worldRegionRepository;

    public function __construct(
        PlayerRepository $playerRepository,
        WorldRepository $worldRepository,
        FleetRepository $fleetRepository,
        BombardmentCooldownRepository $bombardmentCooldownRepository,
        GameUnitRegistry $gameUnitRegistry,
        WorldRegionRepository $worldRegionRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->worldRepository = $worldRepository;
        $this->fleetRepository = $fleetRepository;
        $this->bombardmentCooldownRepository = $bombardmentCooldownRepository;
        $this->gameUnitRegistry = $gameUnitRegistry;
        $this->worldRegionRepository = $worldRegionRepository;
    }

    public function create(WorldGeneratorService $worldGeneratorService): Response
    {
        $validWorlds = [];
        $worlds = $this->worldRepository->findByPublic(true);
        foreach ($worlds as $world) {
            if ($world->isJoinableForUser($this->getGameUser())) {
                $validWorlds[] = $world;
            }
        }

        if (count($validWorlds) > 0) {
            $this->addFlash('error', 'There are active worlds, no need to create a new one at this moment');
            return $this->redirectToRoute('Game/SelectWorld', [], 302);
        }

        $this->addFlash('success', 'Successfully created a new world!');
        $worldGeneratorService->generateBasicWorld();

        return $this->redirectToRoute('Game/SelectWorld', [], 302);
    }

    public function worldsApi(): JsonResponse
    {
        $user = $this->getGameUser();

        $myWorlds = [];
        foreach ($user->getPlayers() as $player) {
            $world = $player->getWorld();
            $myWorlds[] = [
                'playerId' => $player->getId(),
                'playerName' => $player->getName(),
                'worldName' => $world->getName(),
            ];
        }

        $joinableWorlds = [];
        $worlds = $this->worldRepository->findByPublic(true);
        foreach ($worlds as $world) {
            if ($world->isJoinableForUser($user)) {
                $joinableWorlds[] = [
                    'worldId' => $world->getId(),
                    'worldName' => $world->getName(),
                    'description' => $world->getDescription(),
                    'maxPlayers' => $world->getMaxPlayers(),
                    'currentPlayers' => $world->getPlayers()->count(),
                ];
            }
        }

        return new JsonResponse([
            'success' => true,
            'myWorlds' => $myWorlds,
            'joinableWorlds' => $joinableWorlds,
        ]);
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

    public function start(
        Request $request,
        int $worldId,
        PlayerSetupService $playerSetupService,
        RequestStack $requestStack
    ): Response {
        $name = (string) $request->request->get('name');

        $user = $this->getGameUser();
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            return $this->redirectToRoute('Game/SelectWorld', [], 302);
        }

        foreach ($user->getPlayers() as $player) {
            if ($player->getWorld()->getId() === $worldId) {
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

        $playerSetupService->setupNewPlayer($player, $world);
        $requestStack->getSession()->set('playerId', $player->getId());

        return $this->redirectToRoute('Game/WorldMap', ['welcome' => 1], 302);
    }

    public function image(int $worldId): Response
    {
        $world = $this->worldRepository->find($worldId);
        $imageData = $world?->getImageData();
        if ($imageData === null) {
            throw new NotFoundHttpException('World image not found');
        }

        $response = new Response($imageData);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->headers->set('Cache-Control', 'public, max-age=86400');

        return $response;
    }

    public function worldMap(Request $request): Response
    {
        $player = $this->getPlayer();
        $world = $player->getWorld();

        $regions = $this->getWorldRegionsData($world, $player);
        $fleets = $this->getPlayerFleetsData($player);
        $bombardments = $this->getActiveBombardmentCooldowns($player);

        return $this->render(
            'v2/game/world.html.twig',
            [
                'regions' => $regions,
                'player' => $player,
                'fleets' => $fleets,
                'bombardments' => $bombardments,
                'showWelcome' => $request->query->getBoolean('welcome'),
            ]
        );
    }

    /**
     * @return list<array{
     *   x: int, y: int, id: int, type: string, image: string, hasOwner: bool,
     *   isYours: bool, ownerName: string|null, isVisible: bool,
     *   units: array<string, mixed>
     * }>
     */
    private function getWorldRegionsData(World $world, Player $player): array
    {
        // Load the units and constructions of the player's regions upfront, instead of querying per region.
        // This must run before anything reads those collections, such as the radar lookup below.
        $this->worldRegionRepository->findByPlayerWithUnitsAndConstructions($player);

        $playerRegions = $this->getPlayerRegionCoordinates($player);
        $visibleRegions = $this->calculateVisibleRegions($playerRegions, $this->getRadarRegions($player));

        $regions = [];
        foreach ($world->getWorldRegions() as $region) {
            $isYours = $region->getPlayer() !== null && $region->getPlayer()->getId() === $player->getId();
            $coordinates = $region->getX() . ',' . $region->getY();
            // If player has no regions, make everything visible (no fog of war yet)
            $isVisible = $playerRegions === [] || isset($visibleRegions[$coordinates]);

            $regionData = [
                'x' => $region->getX(),
                'y' => $region->getY(),
                'id' => $region->getId(),
                'type' => $region->getType(),
                'image' => $this->getRegionImage($region->getType()),
                'hasOwner' => $region->getPlayer() !== null,
                'isYours' => $isYours,
                'ownerName' => $region->getPlayer()?->getName(),
                'isVisible' => $isVisible,
                'units' => [],
            ];

            if ($isYours) {
                $regionData['units'] = $this->gameUnitRegistry->getRegionUnitSummary($region);
            } elseif ($isVisible && $region->getPlayer() !== null) {
                $regionData['units'] = $this->buildMaskedUnits(
                    $this->gameUnitRegistry->getRegionUnitCategoriesPresence($region)
                );
            }

            $regions[] = $regionData;
        }

        return $regions;
    }

    /**
     * Get coordinates of all regions owned by the player
     * @return array<string, true>
     */
    private function getPlayerRegionCoordinates(Player $player): array
    {
        $coordinates = [];
        foreach ($player->getWorldRegions() as $region) {
            $coordinates[$region->getX() . ',' . $region->getY()] = true;
        }
        return $coordinates;
    }

    /**
     * Coordinates of owned regions that have a Radar Station, mapped to the radar's level.
     * @return array<string, int>
     */
    private function getRadarRegions(Player $player): array
    {
        $radarRegions = [];
        foreach ($player->getWorldRegions() as $region) {
            $level = $region->getUnitLevel(GameUnitEnum::RADAR_STATION);
            if ($level > 0) {
                $radarRegions[$region->getX() . ',' . $region->getY()] = $level;
            }
        }

        return $radarRegions;
    }

    /**
     * Calculate which regions are visible: every owned region plus its 1-hex ring, extended
     * to a radius equal to the Radar Station level around regions that have one.
     *
     * @param array<string, true> $playerRegions
     * @param array<string, int> $radarRegions
     * @return array<string, true>
     */
    private function calculateVisibleRegions(array $playerRegions, array $radarRegions): array
    {
        $visibleRegions = $playerRegions;

        // Base 1-hex ring of vision around every owned region.
        foreach (array_keys($playerRegions) as $coordString) {
            [$x, $y] = explode(',', $coordString);
            foreach ($this->getHexNeighbors((int)$x, (int)$y) as $neighbor) {
                $visibleRegions[$neighbor] = true;
            }
        }

        // Radar stations extend vision to a radius equal to their level.
        foreach ($radarRegions as $coordString => $level) {
            [$x, $y] = explode(',', $coordString);
            $this->revealRings($visibleRegions, (int)$x, (int)$y, $level);
        }

        return $visibleRegions;
    }

    /**
     * The 6 hex neighbors of a tile (pointy-top, odd-r offset; odd rows shifted right).
     * @return list<string>
     */
    private function getHexNeighbors(int $x, int $y): array
    {
        $neighbors = [
            ($x - 1) . ',' . $y,
            ($x + 1) . ',' . $y,
        ];

        if ($y % 2 === 0) {
            $neighbors[] = ($x - 1) . ',' . ($y - 1);
            $neighbors[] = $x . ',' . ($y - 1);
            $neighbors[] = ($x - 1) . ',' . ($y + 1);
            $neighbors[] = $x . ',' . ($y + 1);
        } else {
            $neighbors[] = $x . ',' . ($y - 1);
            $neighbors[] = ($x + 1) . ',' . ($y - 1);
            $neighbors[] = $x . ',' . ($y + 1);
            $neighbors[] = ($x + 1) . ',' . ($y + 1);
        }

        return $neighbors;
    }

    /**
     * Reveal a hex disk of the given radius (in rings) around a center tile.
     *
     * @param array<string, true> $visibleRegions
     */
    private function revealRings(array &$visibleRegions, int $x, int $y, int $rings): void
    {
        $visited = [$x . ',' . $y => true];
        $frontier = [[$x, $y]];

        for ($ring = 0; $ring < $rings; $ring++) {
            $next = [];
            foreach ($frontier as [$cx, $cy]) {
                foreach ($this->getHexNeighbors($cx, $cy) as $neighbor) {
                    if (isset($visited[$neighbor])) {
                        continue;
                    }
                    $visited[$neighbor] = true;
                    $visibleRegions[$neighbor] = true;
                    [$nx, $ny] = explode(',', $neighbor);
                    $next[] = [(int)$nx, (int)$ny];
                }
            }
            $frontier = $next;
        }
    }

    /**
     * Build masked unit data that reveals category presence without counts.
     *
     * @param array<string, bool> $presence
     * @return array<string, mixed>
     */
    private function buildMaskedUnits(array $presence): array
    {
        $masked = [];
        foreach ($presence as $category => $hasUnits) {
            $masked[$category] = $hasUnits ? -1 : 0;
        }
        $masked['details'] = null;
        $masked['masked'] = true;

        return $masked;
    }

    /**
     * Get fleet data for the player
     * @return array<int, array<string, mixed>>
     */
    private function getPlayerFleetsData(Player $player): array
    {
        $fleets = [];
        $currentTime = time();

        foreach ($this->fleetRepository->findByPlayer($player) as $fleet) {
            $sourceRegion = $fleet->getWorldRegion();
            $targetRegion = $fleet->getTargetWorldRegion();
            $arriveTime = $fleet->getTimestampArrive();
            $hasArrived = $currentTime >= $arriveTime;

            // Check if target region belongs to the player
            $targetIsYours = $targetRegion->getPlayer() !== null
                && $targetRegion->getPlayer()->getId() === $player->getId();

            // Get unit details
            $units = [];
            $totalUnitCount = 0;
            foreach ($fleet->getFleetUnits() as $fleetUnit) {
                $amount = $fleetUnit->getAmount();
                $totalUnitCount += $amount;
                $gameUnit = $this->gameUnitRegistry->find($fleetUnit->getGameUnit());
                $units[] = [
                    'name' => $gameUnit->getName(),
                    'amount' => $amount,
                ];
            }

            $fleets[] = [
                'id' => $fleet->getId(),
                'sourceX' => $sourceRegion->getX(),
                'sourceY' => $sourceRegion->getY(),
                'targetX' => $targetRegion->getX(),
                'targetY' => $targetRegion->getY(),
                'targetRegionId' => $targetRegion->getId(),
                'timestampArrive' => $arriveTime,
                'hasArrived' => $hasArrived,
                'eta' => $hasArrived ? 0 : $arriveTime - $currentTime,
                'unitCount' => $totalUnitCount,
                'units' => $units,
                'targetIsYours' => $targetIsYours,
            ];
        }

        return $fleets;
    }

    /**
     * @return list<array{
     *   sourceX: int, sourceY: int, targetX: int, targetY: int,
     *   cooldownUntil: int, remainingSeconds: int
     * }>
     */
    private function getActiveBombardmentCooldowns(Player $player): array
    {
        $bombardments = [];
        $currentTime = time();

        foreach ($this->bombardmentCooldownRepository->findActiveByPlayer($player) as $cooldown) {
            $bombardments[] = [
                'sourceX' => $cooldown->getWorldRegion()->getX(),
                'sourceY' => $cooldown->getWorldRegion()->getY(),
                'targetX' => $cooldown->getTargetWorldRegion()->getX(),
                'targetY' => $cooldown->getTargetWorldRegion()->getY(),
                'cooldownUntil' => $cooldown->getCooldownUntil(),
                'remainingSeconds' => max(0, $cooldown->getCooldownUntil() - $currentTime),
            ];
        }

        return $bombardments;
    }

    private function getRegionImage(string $type): string
    {
        return $type . '.png';
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;

final class NetWorthUpdaterService
{
    private FederationRepository $federationRepository;
    private PlayerRepository $playerRepository;
    private NetWorthCalculator $netWorthCalculator;

    public function __construct(
        FederationRepository $federationRepository,
        PlayerRepository $playerRepository,
        NetWorthCalculator $netWorthCalculator
    ) {
        $this->federationRepository = $federationRepository;
        $this->playerRepository = $playerRepository;
        $this->netWorthCalculator = $netWorthCalculator;
    }

    public function updateNetWorthForPlayer(Player $player): void
    {
        $netWorth = $this->netWorthCalculator->calculateNetWorthForPlayer($player);
        $player->setNetWorth($netWorth);

        if ($player->getFederation() !== null) {
            $federation = $player->getFederation();
            $federationNetWorth = 0;
            $federationRegions = 0;

            foreach ($federation->getPlayers() as $federationPlayer) {
                $federationNetWorth += $this->netWorthCalculator->calculateNetWorthForPlayer($federationPlayer);
                $federationRegions += count($federationPlayer->getWorldRegions());
            }

            $federation->setNetWorth($federationNetWorth);
            $federation->setRegions($federationRegions);
            $this->federationRepository->save($federation);
        }

        $this->playerRepository->save($player);
    }
}

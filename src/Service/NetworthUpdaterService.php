<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Util\NetworthCalculator;

final class NetworthUpdaterService
{
    private FederationRepository $federationRepository;
    private PlayerRepository $playerRepository;
    private NetworthCalculator $networthCalculator;

    public function __construct(
        FederationRepository $federationRepository,
        PlayerRepository $playerRepository,
        NetworthCalculator $networthCalculator
    ) {
        $this->federationRepository = $federationRepository;
        $this->playerRepository = $playerRepository;
        $this->networthCalculator = $networthCalculator;
    }

    public function updateNetworthForPlayer(Player $player): void
    {
        $networth = $this->networthCalculator->calculateNetworthForPlayer($player);
        $player->setNetworth($networth);

        if ($player->getFederation() !== null) {
            $federation = $player->getFederation();
            $federationNetworth = 0;
            $federationRegions = 0;

            foreach ($federation->getPlayers() as $federationPlayer) {
                $federationNetworth += $this->networthCalculator->calculateNetworthForPlayer($federationPlayer);
                $federationRegions += count($federationPlayer->getWorldRegions());
            }

            $federation->setNetworth($federationNetworth);
            $federation->setRegions($federationRegions);
            $this->federationRepository->save($federation);
        }

        $this->playerRepository->save($player);
    }
}

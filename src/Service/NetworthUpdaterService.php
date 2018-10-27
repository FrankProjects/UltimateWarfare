<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Util\NetworthCalculator;

final class NetworthUpdaterService
{
    /**
     * @var FederationRepository
     */
    private $federationRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * NetworthUpdaterService constructor.
     *
     * @param FederationRepository $federationRepository
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        FederationRepository $federationRepository,
        PlayerRepository $playerRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param Player $player
     */
    public function updateNetworthForPlayer(Player $player): void
    {
        $networth = NetworthCalculator::calculateNetworthForPlayer($player);
        $player->setNetworth($networth);

        if ($player->getFederation() !== null) {
            $federation = $player->getFederation();
            $federationNetworth = 0;

            foreach ($federation->getPlayers() as $federationPlayer) {
                $federationNetworth += NetworthCalculator::calculateNetworthForPlayer($federationPlayer);
            }

            $federation->setNetworth($federationNetworth);
            $this->federationRepository->save($federation);
        }

        $this->playerRepository->save($player);
    }
}

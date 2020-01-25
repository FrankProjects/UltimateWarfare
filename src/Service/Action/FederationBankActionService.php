<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use RuntimeException;

final class FederationBankActionService
{
    private FederationRepository $federationRepository;
    private FederationNewsRepository $federationNewsRepository;
    private PlayerRepository $playerRepository;

    public function __construct(
        FederationRepository $federationRepository,
        FederationNewsRepository $federationNewsRepository,
        PlayerRepository $playerRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationNewsRepository = $federationNewsRepository;
        $this->playerRepository = $playerRepository;
    }

    public function deposit(Player $player, array $resources): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RunTimeException("You are not in a Federation!");
        }

        $resourceString = '';
        foreach ($resources as $resourceName => $amount) {
            if (!GameResource::isValid($resourceName)) {
                continue;
            }

            $amount = intval($amount);
            if ($amount <= 0) {
                continue;
            }

            if ($amount > $player->getResources()->$resourceName) {
                throw new RunTimeException("You don't have enough {$resourceName}!");
            }

            $player->getResources()->$resourceName -= $amount;
            $federation->getResources()->$resourceName += $amount;

            $resourceString = $this->addToResourceString($resourceString, $amount, $resourceName);
        }

        if ($resourceString !== '') {
            $news = "{$player->getName()} deposited {$resourceString} to the Federation Bank";
            $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
            $this->federationNewsRepository->save($federationNews);

            $this->playerRepository->save($player);
            $this->federationRepository->save($federation);
        }
    }

    public function withdraw(Player $player, array $resources): void
    {
        $this->ensureFederationEnabled($player);

        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RunTimeException("You are not in a Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_CAPTAIN) {
            throw new RunTimeException("You don't have permission to use the Federation Bank!");
        }

        $resourceString = '';
        foreach ($resources as $resourceName => $amount) {
            if (!GameResource::isValid($resourceName)) {
                continue;
            }

            $amount = intval($amount);
            if ($amount <= 0) {
                continue;
            }

            if ($amount > $player->getFederation()->getResources()->$resourceName) {
                throw new RunTimeException("Federation Bank doesn't have enough {$resourceName}!");
            }

            $player->getResources()->$resourceName += $amount;
            $federation->getResources()->$resourceName -= $amount;

            $resourceString = $this->addToResourceString($resourceString, $amount, $resourceName);
        }

        if ($resourceString !== '') {
            $news = "{$player->getName()} withdrew {$resourceString} from the Federation Bank";
            $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
            $this->federationNewsRepository->save($federationNews);

            $this->playerRepository->save($player);
            $this->federationRepository->save($federation);
        }
    }

    private function ensureFederationEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getFederation()) {
            throw new RunTimeException("Federations not enabled!");
        }
    }

    private function addToResourceString(string $resourceString, int $amount, string $resourceName): string
    {
        if ($resourceString !== '') {
            $resourceString .= ', ';
        }
        $resourceString .= $amount . ' ' . $resourceName;

        return $resourceString;
    }
}

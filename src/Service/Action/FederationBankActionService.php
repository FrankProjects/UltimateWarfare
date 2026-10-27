<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\EntityLockRepository;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use RuntimeException;

final class FederationBankActionService
{
    private FederationRepository $federationRepository;
    private FederationNewsRepository $federationNewsRepository;
    private PlayerRepository $playerRepository;
    private EntityLockRepository $entityLockRepository;

    public function __construct(
        FederationRepository $federationRepository,
        FederationNewsRepository $federationNewsRepository,
        PlayerRepository $playerRepository,
        EntityLockRepository $entityLockRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationNewsRepository = $federationNewsRepository;
        $this->playerRepository = $playerRepository;
        $this->entityLockRepository = $entityLockRepository;
    }

    /**
     * @param array<string, string> $resources
     */
    public function deposit(Player $player, array $resources): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("You are not in a Federation!");
        }

        // The player was reloaded under lock; make sure the federation is still theirs, then lock it too
        if ($player->getFederation() !== $federation || !$this->entityLockRepository->lockForUpdate($federation)) {
            throw new RuntimeException("You are not in a Federation!");
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
            $resourceAmount = $player->getResources()->getValueByName($resourceName);
            if ($amount > $resourceAmount) {
                throw new RuntimeException("You don't have enough {$resourceName}!");
            }

            $player->getResources()->setValueByName($resourceName, $resourceAmount - $amount);
            $federationResourceAmount = $federation->getResources()->getValueByName($resourceName);
            $federation->getResources()->setValueByName($resourceName, $federationResourceAmount + $amount);

            $resourceString = $this->addToResourceString($resourceString, $amount, $resourceName);
        }

        if ($resourceString !== '') {
            $news = "{$player->getName()} deposited {$resourceString} to the Federation Bank";
            $federationNews = FederationNews::createForFederation($federation, $news);
            $this->federationNewsRepository->save($federationNews);

            $this->playerRepository->save($player);
            $this->federationRepository->save($federation);
        } else {
            throw new RuntimeException("No resources selected to deposit");
        }
    }

    /**
     * @param array<string, string> $resources
     */
    public function withdraw(Player $player, array $resources): void
    {
        $this->ensureFederationEnabled($player);

        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("You are not in a Federation!");
        }

        // The player was reloaded under lock; make sure the federation is still theirs, then lock it too
        if ($player->getFederation() !== $federation || !$this->entityLockRepository->lockForUpdate($federation)) {
            throw new RuntimeException("You are not in a Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_CAPTAIN) {
            throw new RuntimeException("You don't have permission to use the Federation Bank!");
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

            $federationResourceAmount = $federation->getResources()->getValueByName($resourceName);
            if ($amount > $federationResourceAmount) {
                throw new RuntimeException("Federation Bank doesn't have enough {$resourceName}!");
            }

            $resourceAmount = $player->getResources()->getValueByName($resourceName);
            $player->getResources()->setValueByName($resourceName, $resourceAmount + $amount);
            $federation->getResources()->setValueByName($resourceName, $federationResourceAmount - $amount);

            $resourceString = $this->addToResourceString($resourceString, $amount, $resourceName);
        }

        if ($resourceString !== '') {
            $news = "{$player->getName()} withdrew {$resourceString} from the Federation Bank";
            $federationNews = FederationNews::createForFederation($federation, $news);
            $this->federationNewsRepository->save($federationNews);

            $this->playerRepository->save($player);
            $this->federationRepository->save($federation);
        }
    }

    private function ensureFederationEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getFederation()) {
            throw new RuntimeException("Federations not enabled!");
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

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Entity\GameResource;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Service\PlayerStateUpdaterService;
use RuntimeException;

final class FederationActionService
{
    private FederationRepository $federationRepository;
    private FederationNewsRepository $federationNewsRepository;
    private PlayerRepository $playerRepository;
    private ReportRepository $reportRepository;
    private PlayerStateUpdaterService $playerStateUpdaterService;

    public function __construct(
        FederationRepository $federationRepository,
        FederationNewsRepository $federationNewsRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository,
        PlayerStateUpdaterService $playerStateUpdaterService
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationNewsRepository = $federationNewsRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
        $this->playerStateUpdaterService = $playerStateUpdaterService;
    }

    public function createFederation(Player $player, string $federationName): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederation() !== null) {
            throw new RuntimeException("You are already in a Federation!");
        }

        if ($this->federationRepository->findByNameAndWorld($federationName, $player->getWorld()) !== null) {
            throw new RuntimeException("Federation with this name already exist!");
        }

        $federation = Federation::createForPlayer($player, $federationName);
        $this->federationRepository->save($federation);

        $player->setFederation($federation);
        $player->setFederationHierarchy(Player::FEDERATION_HIERARCHY_GENERAL);

        $this->playerRepository->save($player);
    }

    /**
     * @param array<string, string> $resources
     */
    public function sendAid(Player $player, int $playerId, array $resources): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if ($player->getId() === $playerId) {
            throw new RuntimeException("You can't send to yourself!");
        }

        $aidPlayer = $this->playerRepository->find($playerId);
        if ($aidPlayer === null) {
            throw new RuntimeException("Aid player does not exist!");
        }

        // The sender is locked for the whole request; settle and lock the receiver as well
        $this->playerStateUpdaterService->updateStateForPlayer($aidPlayer, time());

        $aidPlayerFederation = $aidPlayer->getFederation();
        if ($aidPlayerFederation === null || $aidPlayerFederation->getId() !== $federation->getId()) {
            throw new RuntimeException("Player is not in your Federation!");
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
            $aidPlayerResourceAmount = $aidPlayer->getResources()->getValueByName($resourceName);
            $aidPlayer->getResources()->setValueByName($resourceName, $aidPlayerResourceAmount + $amount);

            if ($resourceString !== '') {
                $resourceString .= ', ';
            }
            $resourceString .= $amount . ' ' . $resourceName;
        }

        if ($resourceString !== '') {
            $news = "{$player->getName()} has sent {$resourceString} to {$aidPlayer->getName()}";
            $federationNews = FederationNews::createForFederation($federation, $news);
            $this->federationNewsRepository->save($federationNews);

            $aidPlayerNotifications = $aidPlayer->getNotifications();
            $aidPlayerNotifications->setAid(true);
            $aidPlayer->setNotifications($aidPlayerNotifications);
            $this->playerRepository->save($aidPlayer);
            $this->playerRepository->save($player);

            $reportString = "{$player->getName()} has sent {$resourceString} to you";
            $report = Report::createForPlayer($aidPlayer, time(), Report::TYPE_AID, $reportString);
            $this->reportRepository->save($report);

            $reportString = "You have send {$resourceString} to {$aidPlayer->getName()}";
            $report = Report::createForPlayer($player, time(), Report::TYPE_AID, $reportString);
            $this->reportRepository->save($report);
        }
    }

    public function removeFederation(Player $player): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to remove the Federation!");
        }

        $this->federationRepository->remove($federation);
    }

    public function changeFederationName(Player $player, string $federationName): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to change the Federation name!");
        }

        if ($federationName === '') {
            throw new RuntimeException("Federation name can not be empty");
        }

        if ($this->federationRepository->findByNameAndWorld($federationName, $player->getWorld()) !== null) {
            throw new RuntimeException("Federation name already exist!");
        }

        $federation->setName($federationName);
        $this->federationRepository->save($federation);
    }

    public function leaveFederation(Player $player): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if (
            $player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_RECRUIT
            || $player->getFederationHierarchy() === Player::FEDERATION_HIERARCHY_GENERAL
        ) {
            throw new RuntimeException("You are not allowed to leave the Federation with this rank!");
        }

        $news = "{$player->getName()} has left the Federation.";
        $federationNews = FederationNews::createForFederation($federation, $news);
        $this->federationNewsRepository->save($federationNews);

        $player->setFederation(null);
        $player->setFederationHierarchy(0);
        $this->playerRepository->save($player);

        $federation->setNetWorth($federation->getNetWorth() - $player->getNetWorth());
        $federation->setRegions($federation->getRegions() - count($player->getWorldRegions()));
        $this->federationRepository->save($federation);
    }

    public function kickPlayer(Player $player, int $playerId): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to kick a player!");
        }

        if ($player->getId() === $playerId) {
            throw new RuntimeException("You can't kick yourself!");
        }

        $kickPlayer = $this->playerRepository->find($playerId);
        if ($kickPlayer === null) {
            throw new RuntimeException("Kick player does not exist!");
        }

        $kickPlayerFederation = $kickPlayer->getFederation();
        if ($kickPlayerFederation === null || $kickPlayerFederation->getId() !== $federation->getId()) {
            throw new RuntimeException("Player is not in your Federation!");
        }

        $kickPlayer->setFederation(null);
        $kickPlayer->setFederationHierarchy(0);
        $this->playerRepository->save($kickPlayer);

        $news = "{$player->getName()} kicked {$kickPlayer->getName()} from the Federation.";
        $federationNews = FederationNews::createForFederation($federation, $news);
        $this->federationNewsRepository->save($federationNews);

        $federation->setNetWorth($federation->getNetWorth() - $kickPlayer->getNetWorth());
        $federation->setRegions($federation->getRegions() - count($kickPlayer->getWorldRegions()));
        $this->federationRepository->save($federation);

        $reportString = "You have been kicked from Federation {$federation->getName()}";
        $report = Report::createForPlayer($kickPlayer, time(), Report::TYPE_GENERAL, $reportString);
        $this->reportRepository->save($report);
    }

    public function updateLeadershipMessage(Player $player, string $message): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to update the leadership message!");
        }

        $federation->setLeaderMessage($message);
        $this->federationRepository->save($federation);
    }

    public function changePlayerHierarchy(Player $player, int $playerId, int $role): void
    {
        $this->ensureFederationEnabled($player);
        $federation = $player->getFederation();
        if ($federation === null) {
            throw new RuntimeException("Player is not in Federation!");
        }

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to change ranks!");
        }

        if ($player->getId() === $playerId) {
            throw new RuntimeException("You can't change your own rank!");
        }

        $changePlayer = $this->playerRepository->find($playerId);
        if ($changePlayer === null) {
            throw new RuntimeException("Player does not exist!");
        }

        $changePlayerFederation = $changePlayer->getFederation();
        if ($changePlayerFederation === null || $changePlayerFederation->getId() !== $federation->getId()) {
            throw new RuntimeException("Player is not in your Federation!");
        }

        if ($role < Player::FEDERATION_HIERARCHY_RECRUIT || $role > Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("Invalid role!");
        }

        $changePlayer->setFederationHierarchy($role);
        $this->playerRepository->save($changePlayer);

        if ($role === Player::FEDERATION_HIERARCHY_GENERAL) {
            $this->transferFederationOwnership($player, $changePlayer, $federation);
        }
    }

    /**
     * A Federation has a single General; promoting another player to General
     * hands over the Federation and demotes the current General to Staff General.
     */
    private function transferFederationOwnership(Player $player, Player $newOwner, Federation $federation): void
    {
        $player->setFederationHierarchy(Player::FEDERATION_HIERARCHY_STAFF_GENERAL);
        $this->playerRepository->save($player);

        $news = "{$player->getName()} has handed over the Federation to {$newOwner->getName()}.";
        $federationNews = FederationNews::createForFederation($federation, $news);
        $this->federationNewsRepository->save($federationNews);

        $reportString = "{$player->getName()} has made you the General of Federation {$federation->getName()}";
        $report = Report::createForPlayer($newOwner, time(), Report::TYPE_GENERAL, $reportString);
        $this->reportRepository->save($report);
    }

    private function ensureFederationEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getFederation()) {
            throw new RuntimeException("Federations not enabled!");
        }
    }
}

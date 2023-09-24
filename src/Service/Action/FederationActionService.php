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
use RuntimeException;

final class FederationActionService
{
    private FederationRepository $federationRepository;
    private FederationNewsRepository $federationNewsRepository;
    private PlayerRepository $playerRepository;
    private ReportRepository $reportRepository;

    public function __construct(
        FederationRepository $federationRepository,
        FederationNewsRepository $federationNewsRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationNewsRepository = $federationNewsRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
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

        if ($player->getId() === $playerId) {
            throw new RuntimeException("You can't send to yourself!");
        }

        $aidPlayer = $this->playerRepository->find($playerId);
        if ($aidPlayer === null || $aidPlayer->getFederation()->getId() !== $player->getFederation()->getId()) {
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
            $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
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

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to remove the Federation!");
        }

        $this->federationRepository->remove($player->getFederation());
    }

    public function changeFederationName(Player $player, string $federationName): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to change the Federation name!");
        }

        if ($this->federationRepository->findByNameAndWorld($federationName, $player->getWorld()) !== null) {
            throw new RuntimeException("Federation name already exist!");
        }

        $federation = $player->getFederation();
        $federation->setName($federationName);
        $this->federationRepository->save($federation);
    }

    public function leaveFederation(Player $player): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < 1 || $player->getFederationHierarchy() == 10) {
            throw new RuntimeException("You are not allowed to leave the Federation with this rank!");
        }

        $federation = $player->getFederation();
        $news = "{$player->getName()} has left the Federation.";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $player->setFederation(null);
        $player->setFederationHierarchy(0);
        $this->playerRepository->save($player);

        $federation->setNetworth($federation->getNetworth() - $player->getNetworth());
        $federation->setRegions($federation->getRegions() - count($player->getWorldRegions()));
        $this->federationRepository->save($federation);
    }

    public function kickPlayer(Player $player, int $playerId): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to kick a player!");
        }

        if ($player->getId() === $playerId) {
            throw new RuntimeException("You can't kick yourself!");
        }

        $kickPlayer = $this->playerRepository->find($playerId);
        if ($kickPlayer === null || $kickPlayer->getFederation()->getId() !== $player->getFederation()->getId()) {
            throw new RuntimeException("Player is not in your Federation!");
        }

        $kickPlayer->setFederation(null);
        $kickPlayer->setFederationHierarchy(0);
        $this->playerRepository->save($kickPlayer);

        $news = "{$player->getName()} kicked {$kickPlayer->getName()} from the Federation.";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $federation = $player->getFederation();
        $federation->setNetworth($federation->getNetworth() - $kickPlayer->getNetworth());
        $federation->setRegions($federation->getRegions() - count($kickPlayer->getWorldRegions()));
        $this->federationRepository->save($federation);

        $reportString = "You have been kicked from Federation {$federation->getName()}";
        $report = Report::createForPlayer($kickPlayer, time(), Report::TYPE_GENERAL, $reportString);
        $this->reportRepository->save($report);
    }

    public function updateLeadershipMessage(Player $player, string $message): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to update the leadership message!");
        }

        $federation = $player->getFederation();
        $federation->setLeaderMessage($message);
        $this->federationRepository->save($federation);
    }

    public function changePlayerHierarchy(Player $player, int $playerId, int $role): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RuntimeException("You don't have permission to change ranks!");
        }

        $changePlayer = $this->playerRepository->find($playerId);
        if ($changePlayer === null || $changePlayer->getFederation()->getId() !== $player->getFederation()->getId()) {
            throw new RuntimeException("Player is not in your Federation!");
        }

        if ($role < 1 || $role > 10) {
            throw new RuntimeException("Invalid role!");
        }

        $changePlayer->setFederationHierarchy($role);
        $this->playerRepository->save($changePlayer);
        /**
         * XXX TODO!
         *
         * if ($role == 10)
         * $sql = $db->query("UPDATE player SET fedlvl = $rank WHERE id = $fed_player;");
         * $sql2 = $db->query("UPDATE player SET fedlvl = 9 WHERE id = $player_id;");
         *
         * <table class="table text">
         * <tr><td class="tabletop"><b>Changing Federation Owner</b></td></tr>
         *
         * <form action="" method="post" />
         * <tr><td>
         * <b>Are you sure you wanna do this?<br />
         * By accepting this you will give the federation to another player, and will lower your rank to Staff General!<br /><br />
         *
         * <input type="hidden" name="rank" value="<?php echo"$rank"; ?>">
         * <input type="hidden" name="player" value="<?php echo"$fed_player"; ?>">
         * <br />
         * <input type="submit" name="submit" value="Accept">
         * </td></tr>
         * </table>
         */
    }

    private function ensureFederationEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getFederation()) {
            throw new RuntimeException("Federations not enabled!");
        }
    }
}

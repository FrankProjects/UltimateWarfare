<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use RuntimeException;

final class FederationActionService
{
    /**
     * @var FederationRepository
     */
    private $federationRepository;

    /**
     * @var FederationNewsRepository
     */
    private $federationNewsRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * FederationActionService constructor.
     *
     * @param FederationRepository $federationRepository
     * @param FederationNewsRepository $federationNewsRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     */
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

    /**
     * @param Player $player
     * @param string $federationName
     */
    public function createFederation(Player $player, string $federationName): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederation() !== null) {
            throw new RunTimeException("You are already in a Federation!");
        }

        if ($this->federationRepository->findByNameAndWorld($federationName, $player->getWorld()) !== null) {
            throw new RunTimeException("Federation with this name already exist!");
        }

        $federation = Federation::createForPlayer($player, $federationName);
        $this->federationRepository->save($federation);

        $player->setFederation($federation);
        $player->setFederationHierarchy(Player::FEDERATION_HIERARCHY_GENERAL);

        $this->playerRepository->save($player);
    }

    /**
     * @param Player $player
     * @param int $playerId
     * @param array $resources
     */
    public function sendAid(Player $player, int $playerId, array $resources): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getId() === $playerId) {
            throw new RunTimeException("You can't send to yourself!");
        }

        $aidPlayer = $this->playerRepository->find($playerId);
        if ($aidPlayer === null || $aidPlayer->getFederation()->getId() !== $player->getFederation()->getId()) {
            throw new RunTimeException("Player is not in your Federation!");
        }

        $resourceString = '';
        foreach ($resources as $resourceName => $amount) {
            $this->ensureValidResourcename($resourceName);

            if ($amount < 0) {
                throw new RunTimeException("You can't send negative {$resourceName}!");
            }

            if ($amount > $player->getResources()->$resourceName) {
                throw new RunTimeException("You don't have enough {$resourceName}!");
            }

            if ($resourceString !== '') {
                $resourceString .= ', ';
            }
            $resourceString .= $amount . ' ' . $resourceName;
        }


        /**
         * XXX TODO!
         *
         * $db->query("UPDATE player set cash = cash - $cash, wood = wood - $wood, steel = steel - $steel, food = food - $food WHERE id = $player_id");
         * $db->query("UPDATE player set cash = cash + $cash, wood = wood + $wood, steel = steel + $steel, food = food + $food WHERE id = $to_id");
         */

        $news = "{$player->getName()} has sent {$resourceString} to {$aidPlayer->getName()}";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $aidPlayerNotifications = $aidPlayer->getNotifications();
        $aidPlayerNotifications->setAid(true);
        $aidPlayer->setNotifications($aidPlayerNotifications);
        $this->playerRepository->save($aidPlayer);

        $reportString = "{$player->getName()} has sent {$resourceString} to you";
        $report = Report::createForPlayer($aidPlayer, time(), Report::TYPE_AID, $reportString);
        $this->reportRepository->save($report);

        $reportString = "You have send {$resourceString} to {$aidPlayer->getName()}";
        $report = Report::createForPlayer($player, time(), Report::TYPE_AID, $reportString);
        $this->reportRepository->save($report);
    }

    /**
     * @param Player $player
     */
    public function removeFederation(Player $player): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RunTimeException("You don't have permission to remove the Federation!");
        }

        $this->federationRepository->remove($player->getFederation());
    }

    /**
     * @param Player $player
     * @param string $federationName
     */
    public function changeFederationName(Player $player, string $federationName): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RunTimeException("You don't have permission to change the Federation name!");
        }

        if ($this->federationRepository->findByNameAndWorld($federationName, $player->getWorld()) !== null) {
            throw new RunTimeException("Federation name already exist!");
        }

        $federation = $player->getFederation();
        $federation->setName($federationName);
        $this->federationRepository->save($federation);
    }

    /**
     * @param Player $player
     */
    public function leaveFederation(Player $player): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < 1 || $player->getFederationHierarchy() == 10) {
            throw new RunTimeException("You are not allowed to leave the Federation with this rank!");
        }

        $federation = $player->getFederation();
        $news = "{$player->getName()} has left the Federation.";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $player->setFederation(null);
        $player->setFederationHierarchy(0);
        $this->playerRepository->save($player);

        $federation->setNetworth($federation->getNetworth() - $player->getNetworth());
        $federation->setRegions($federation->getRegions() - $player->getRegions());
        $this->federationRepository->save($federation);
    }

    /**
     * @param Player $player
     * @param int $playerId
     */
    public function kickPlayer(Player $player, int $playerId): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RunTimeException("You don't have permission to kick a player!");
        }

        if ($player->getId() === $playerId) {
            throw new RunTimeException("You can't kick yourself!");
        }

        $kickPlayer = $this->playerRepository->find($playerId);
        if ($kickPlayer === null || $kickPlayer->getFederation()->getId() !== $player->getFederation()->getId()) {
            throw new RunTimeException("Player is not in your Federation!");
        }

        $kickPlayer->setFederation(null);
        $kickPlayer->setFederationHierarchy(0);
        $this->playerRepository->save($kickPlayer);

        $news = "{$player->getName()} kicked {$kickPlayer->getName()} from the Federation.";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $federation = $player->getFederation();
        $federation->setNetworth($federation->getNetworth() - $kickPlayer->getNetworth());
        $federation->setRegions($federation->getRegions() - $kickPlayer->getRegions());
        $this->federationRepository->save($federation);

        $reportString = "You have been kicked from Federation {$federation->getName()}";
        $report = Report::createForPlayer($kickPlayer, time(), Report::TYPE_GENERAL, $reportString);
        $this->reportRepository->save($report);
    }

    /**
     * @param Player $player
     * @param string $message
     */
    public function updateLeadershipMessage(Player $player, string $message): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RunTimeException("You don't have permission to update the leadership message!");
        }

        $federation = $player->getFederation();
        $federation->setLeaderMessage($message);
        $this->federationRepository->save($federation);
    }

    /**
     * @param Player $player
     * @param int $playerId
     * @param int $role
     */
    public function changePlayerHierarchy(Player $player, int $playerId, int $role): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_GENERAL) {
            throw new RunTimeException("You don't have permission to change ranks!");
        }

        $changePlayer = $this->playerRepository->find($playerId);
        if ($changePlayer === null || $changePlayer->getFederation()->getId() !== $player->getFederation()->getId()) {
            throw new RunTimeException("Player is not in your Federation!");
        }

        if ($role < 1 || $role > 10) {
            throw new RunTimeException("Invalid role!");
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
         * <table width="700" border="1" cellspacing="0" cellpadding="0" align="center" class="table text">
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

    /**
     * @param Player $player
     */
    private function ensureFederationEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getFederation()) {
            throw new RunTimeException("Federations not enabled!");
        }
    }

    /**
     * XXX TODO: Fix me
     *
     * @param string $resourceName
     */
    private function ensureValidResourceName(string $resourceName): void
    {
        $validResourceNames = ['cash', 'wood', 'steel', 'food'];
        if (!in_array($resourceName, $validResourceNames)) {
            throw new RunTimeException("Invalid resource type {$resourceName}!");
        }
    }
}
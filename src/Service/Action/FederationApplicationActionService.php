<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\FederationApplication;
use FrankProjects\UltimateWarfare\Entity\FederationNews;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\FederationApplicationRepository;
use FrankProjects\UltimateWarfare\Repository\FederationNewsRepository;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use RuntimeException;

final class FederationApplicationActionService
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
     * @var FederationApplicationRepository
     */
    private $federationApplicationRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * FederationApplicationActionService constructor.
     *
     * @param FederationRepository $federationRepository
     * @param FederationApplicationRepository $federationApplicationRepository
     * @param FederationNewsRepository $federationNewsRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        FederationRepository $federationRepository,
        FederationApplicationRepository $federationApplicationRepository,
        FederationNewsRepository $federationNewsRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->federationApplicationRepository = $federationApplicationRepository;
        $this->federationNewsRepository = $federationNewsRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param Player $player
     * @param int $applicationId
     */
    public function acceptFederationApplication(Player $player, int $applicationId): void
    {
        $this->ensureFederationEnabled($player);

        $federationApplication = $this->getFederationApplication($player, $applicationId);
        if ($federationApplication->getPlayer()->getFederation() !== null) {
            throw new RunTimeException("Player is already in another Federation!");
        }

        if (count($player->getFederation()->getPlayers()) >= $player->getWorld()->getFedLimit()) {
            throw new RunTimeException("Federation members world limit reached!");
        }

        $news = "{$federationApplication->getPlayer()->getName()} has has been accepted into the Federation by {$player->getName()}";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $reportString = "You have been accepted in the Federation {$player->getFederation()->getName()}";
        $report = Report::createForPlayer($federationApplication->getPlayer(), time(), Report::TYPE_GENERAL, $reportString);
        $this->reportRepository->save($report);

        $applicationPlayer = $federationApplication->getPlayer();
        $applicationPlayer->setFederation($federationApplication->getFederation());
        $applicationPlayer->setFederationHierarchy(Player::FEDERATION_HIERARCHY_RECRUIT);
        $applicationPlayerNotifications = $applicationPlayer->getNotifications();
        $applicationPlayerNotifications->setGeneral(true);
        $applicationPlayer->setNotifications($applicationPlayerNotifications);
        $this->playerRepository->save($applicationPlayer);

        $federation = $federationApplication->getFederation();
        $federation->setNetworth($federation->getNetworth() + $federationApplication->getPlayer()->getNetworth());
        $federation->setRegions($federation->getRegions() + $federationApplication->getPlayer()->getRegions());
        $this->federationRepository->save($federation);
        /**

        echo"Player accepted";
*/
        $this->federationApplicationRepository->remove($federationApplication);
    }

    /**
     * @param Player $player
     * @param int $applicationId
     */
    public function rejectFederationApplication(Player $player, int $applicationId): void
    {
        $this->ensureFederationEnabled($player);

        $federationApplication = $this->getFederationApplication($player, $applicationId);

        $news = "{$federationApplication->getPlayer()->getName()} has has been rejected to join the Federation by {$player->getName()}";
        $federationNews = FederationNews::createForFederation($player->getFederation(), $news);
        $this->federationNewsRepository->save($federationNews);

        $reportString = "You have been rejected by the Federation {$player->getFederation()->getName()}";
        $report = Report::createForPlayer($federationApplication->getPlayer(), time(), Report::TYPE_GENERAL, $reportString);
        $this->reportRepository->save($report);
        /**
        echo"Player denied";
         */
        $this->federationApplicationRepository->remove($federationApplication);
    }

    /**
     * @param Player $player
     * @param Federation $federation
     * @param string $application
     */
    public function sendFederationApplication(Player $player, Federation $federation, string $application): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederation() !== null) {
            throw new RunTimeException("You are already in a Federation");
        }

        if ($federation->getWorld()->getId() !== $player->getWorld()->getId()) {
            throw new RunTimeException("Federation is not in your world!");
        }

        $federationApplication = FederationApplication::createForFederation($federation, $player, $application);
        $this->federationApplicationRepository->save($federationApplication);
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
     * @param Player $player
     * @param int $federationApplicationId
     * @return FederationApplication
     */
    private function getFederationApplication(Player $player, int $federationApplicationId): FederationApplication
    {
        $federationApplication = $this->federationApplicationRepository->findByIdAndWorld($federationApplicationId, $player->getWorld());

        if (!$federationApplication) {
            throw new RunTimeException('FederationApplication does not exist!');
        }

        if ($player->getFederation() === null) {
            throw new RunTimeException('You are not in a Federation!');
        }

        if ($player->getFederation()->getId() !== $federationApplication->getFederation()->getId()) {
            throw new RunTimeException('FederationApplication does not belong to your Federation!');
        }

        return $federationApplication;
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor;

final class ResearchProcessor implements Processor
{
    /**
     * @var FederationRepository
     */
    private $federationRepository;

    /**
     * @var ResearchPlayerRepository
     */
    private $researchPlayerRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * ResearchProcessor constructor.
     *
     * @param FederationRepository $federationRepository
     * @param ResearchPlayerRepository $researchPlayerRepository
     * @param PlayerRepository $playerRepository
     * @param ReportRepository $reportRepository
     */
    public function __construct(
        FederationRepository $federationRepository,
        ResearchPlayerRepository $researchPlayerRepository,
        PlayerRepository $playerRepository,
        ReportRepository $reportRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->playerRepository = $playerRepository;
        $this->reportRepository = $reportRepository;
    }

    /**
     * @param int $timestamp
     */
    public function run(int $timestamp): void
    {
        $researches = $this->researchPlayerRepository->getNonActiveCompletedResearch($timestamp);

        foreach ($researches as $researchPlayer) {
            $researchPlayer->setActive(true);

            $researchNetworth = 1250;
            $player = $researchPlayer->getPlayer();
            $player->setNetworth($player->getNetworth() + $researchNetworth);

            $federation = $player->getFederation();

            $research = $researchPlayer->getResearch();
            $finishedTimestamp = $researchPlayer->getTimestamp() + $research->getTimestamp();
            $message = "You successfully researched a new technology: {$research->getName()}";
            $report = Report::createForPlayer($player, $finishedTimestamp, 2, $message);

            if ($federation !== null) {
                $federation->setNetworth($federation->getNetworth() + $researchNetworth);
                $this->federationRepository->save($federation);
            }

            $this->reportRepository->save($report);
            $this->researchPlayerRepository->save($researchPlayer);
            $this->playerRepository->save($player);
        }
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;

interface ReportRepository
{
    /**
     * @param int $id
     * @return Report|null
     */
    public function find(int $id): ?Report;

    /**
     * @return Report[]
     */
    public function findAll(): array;

    /**
     * @param Player $player
     * @param $type
     * @param int $limit
     * @return Report[]
     */
    public function findReportsByType(Player $player, int $type, int $limit = 100): array;

    /**
     * @param Player $player
     * @param int $limit
     * @return Report[]
     */
    public function findReports(Player $player, int $limit = 100): array;

    /**
     * @param Report $report
     */
    public function save(Report $report): void;
}

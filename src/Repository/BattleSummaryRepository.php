<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\BattleSummary;

interface BattleSummaryRepository
{
    public function find(int $id): ?BattleSummary;

    public function save(BattleSummary $battleSummary): void;
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Operation;

interface OperationRepository
{
    /**
     * @return Operation[]
     */
    public function findAll(): array;
}

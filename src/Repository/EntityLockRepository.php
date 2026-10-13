<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

interface EntityLockRepository
{
    /**
     * Re-reads an entity from the database under a row lock, held until the surrounding transaction commits.
     *
     * @return bool False when the entity no longer exists
     */
    public function lockForUpdate(object $entity): bool;
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\World;

interface WorldRepository
{
    /**
     * @param int $id
     * @return World|null
     */
    public function find(int $id): ?World;

    /**
     * @param bool $public
     * @return World[]
     */
    public function findByPublic(bool $public): array;

    /**
     * @param World $world
     */
    public function remove(World $world): void;

    /**
     * @param World $world
     */
    public function save(World $world): void;
}

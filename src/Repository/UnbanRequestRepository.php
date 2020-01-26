<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Entity\User;

interface UnbanRequestRepository
{
    public function find(int $id): ?UnbanRequest;

    /**
     * @return UnbanRequest[]
     */
    public function findAll(): array;

    public function findByUser(User $user): ?UnbanRequest;

    public function remove(UnbanRequest $unbanRequest): void;

    public function save(UnbanRequest $unbanRequest): void;
}

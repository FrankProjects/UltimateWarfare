<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Entity\User;

interface UnbanRequestRepository
{
    /**
     * @return UnbanRequest[]
     */
    public function findAll(): array;

    /**
     * @param User $user
     * @return UnbanRequest|null
     */
    public function findByUser(User $user): ?UnbanRequest;

    /**
     * @param UnbanRequest $unbanRequest
     */
    public function remove(UnbanRequest $unbanRequest): void;

    /**
     * @param UnbanRequest $unbanRequest
     */
    public function save(UnbanRequest $unbanRequest): void;
}

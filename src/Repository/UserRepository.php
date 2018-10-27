<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\User;

interface UserRepository
{
    /**
     * @param string $username
     * @return User|null
     */
    public function loadUserByUsername(string $username): ?User;

    /**
     * @param User $user
     */
    public function save(User $user): void;
}

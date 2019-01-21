<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\User;

interface UserRepository
{
    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?User;


    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @param string $confirmationToken
     * @return User|null
     */
    public function findByConfirmationToken(string $confirmationToken): ?User;

    /**
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User;

    /**
     * @param \DateTime $firstDateTime
     * @param \DateTime $lastDateTime
     * @return User[]
     */
    public function findByLastLogin(\DateTime $firstDateTime, \DateTime $lastDateTime): array;

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

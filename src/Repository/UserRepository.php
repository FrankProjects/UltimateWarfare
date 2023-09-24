<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use DateTime;
use FrankProjects\UltimateWarfare\Entity\User;

interface UserRepository
{
    public function find(int $id): ?User;

    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @return User[]
     */
    public function findAllDisabled(): array;

    /**
     * @return User[]
     */
    public function findAllBanned(): array;

    /**
     * @return User[]
     */
    public function findAllActive(): array;

    public function findByConfirmationToken(string $confirmationToken): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    /**
     * @param DateTime $firstDateTime
     * @param DateTime $lastDateTime
     * @return User[]
     */
    public function findByLastLogin(DateTime $firstDateTime, DateTime $lastDateTime): array;

    public function loadUserByUsername(string $username): ?User;

    public function save(User $user): void;
}

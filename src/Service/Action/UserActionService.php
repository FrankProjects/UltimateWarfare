<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use RuntimeException;

final class UserActionService
{
    private UserRepository $userRepository;

    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function addRoleToUser(User $user, string $role): void
    {
        if ($user->hasRole($role)) {
            throw new RuntimeException('User already has this role');
        }

        $user->addRole($role);
        $this->userRepository->save($user);
    }

    public function removeRoleFromUser(User $user, string $role): void
    {
        if (!$user->hasRole($role)) {
            throw new RuntimeException('User does not have this role');
        }

        $user->removeRole($role);
        $this->userRepository->save($user);
    }
}

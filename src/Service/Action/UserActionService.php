<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use RuntimeException;

final class UserActionService
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserActionService service
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $user
     * @param string $role
     * @return void
     */
    public function addRoleToUser(User $user, string $role): void
    {
        if ($user->hasRole($role)) {
            throw new RuntimeException('User already has this role');
        }

        $user->addRole($role);
        $this->userRepository->save($user);
    }

    /**
     * @param User $user
     * @param string $role
     * @return void
     */
    public function removeRoleFromUser(User $user, string $role): void
    {
        if (!$user->hasRole($role)) {
            throw new RuntimeException('User does not have this role');
        }

        $user->removeRole($role);
        $this->userRepository->save($user);
    }
}

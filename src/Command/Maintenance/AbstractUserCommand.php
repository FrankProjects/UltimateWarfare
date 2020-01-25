<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\Action\UserActionService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractUserCommand extends Command
{
    protected UserRepository $userRepository;
    protected UserActionService $userActionService;

    public function __construct(
        UserRepository $userRepository,
        UserActionService $userActionService
    ) {
        $this->userRepository = $userRepository;
        $this->userActionService = $userActionService;
        parent::__construct();
    }

    protected function getUserByUsername(string $username): User
    {
        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            throw new RuntimeException("User {$username} not found!");
        }

        return $user;
    }
}

<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\Action\UserActionService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractUserCommand extends Command
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var UserActionService
     */
    protected $userActionService;

    /**
     * AbstractUserCommand constructor.
     *
     * @param UserRepository $userRepository
     * @param UserActionService $userActionService
     */
    public function __construct(
        UserRepository $userRepository,
        UserActionService $userActionService
    ) {
        $this->userRepository = $userRepository;
        $this->userActionService = $userActionService;
        parent::__construct();
    }

    /**
     * @param string $username
     * @return User
     */
    protected function getUserByUsername(string $username): User
    {
        $user = $this->userRepository->findByUsername($username);
        if ($user === null) {
            throw new RuntimeException("User {$username} not found!");
        }

        return $user;
    }
}

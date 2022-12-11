<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\User;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\Action\UserActionService;
use FrankProjects\UltimateWarfare\Service\MailService;
use RuntimeException;
use Symfony\Component\Console\Command\Command;

abstract class AbstractUserCommand extends Command
{
    protected UserRepository $userRepository;
    protected UserActionService $userActionService;
    protected MailService $mailService;

    public function __construct(
        UserRepository $userRepository,
        UserActionService $userActionService,
        MailService $mailService
    ) {
        $this->userRepository = $userRepository;
        $this->userActionService = $userActionService;
        $this->mailService = $mailService;
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

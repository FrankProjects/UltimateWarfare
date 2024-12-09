<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\User;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'game:maintenance:make:admin')]
class MakeAdminCommand extends AbstractUserCommand
{
    protected function configure(): void
    {
        $this->setDescription('Upgrade user to admin')
            ->setHelp('Add admin role to user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $username */
        $username = $input->getArgument('username');

        try {
            $user = $this->getUserByUsername($username);
            $this->userActionService->addRoleToUser($user, User::ROLE_ADMIN);
            $output->writeln("Added admin role to {$username}");
        } catch (Throwable $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        return Command::SUCCESS;
    }
}

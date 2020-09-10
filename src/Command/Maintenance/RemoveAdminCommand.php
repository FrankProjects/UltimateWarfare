<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class RemoveAdminCommand extends AbstractUserCommand
{
    protected static $defaultName = 'game:maintenance:remove:admin';

    protected function configure(): void
    {
        $this->setDescription('Downgrade user from admin')
            ->setHelp('Remove the admin role from user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        try {
            $user = $this->getUserByUsername($username);
            $this->userActionService->removeRoleFromUser($user, User::ROLE_ADMIN);
            $output->writeln("Removed admin role from {$username}");
        } catch (Throwable $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class MakeAdminCommand extends AbstractUserCommand
{
    protected static $defaultName = 'game:maintenance:make:admin';

    protected function configure(): void
    {
        $this->setDescription('Upgrade user to admin')
            ->setHelp('Add the admin role to an user...')
            ->addArgument('username', InputArgument::REQUIRED, 'The username');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $username = $input->getArgument('username');

        try {
            $user = $this->getUserByUsername($username);
            $this->userActionService->addRoleToUser($user, User::ROLE_ADMIN);
            $output->writeln("Added admin role to {$username}");
        } catch (Throwable $e) {
            $output->writeln($e->getMessage());

        }
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\User;

use FrankProjects\UltimateWarfare\Service\MailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand(name: 'game:user:send-register-mail')]
class SendRegisterMailCommand extends AbstractUserCommand
{
    protected function configure(): void
    {
        $this->setDescription('Send register email to user')
            ->setHelp('Re-send registration email to user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');

        try {
            $user = $this->getUserByUsername($username);
            $this->mailService->sendRegistrationMail($user);
            $output->writeln("Send registration email to {$username}");
        } catch (Throwable $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        return Command::SUCCESS;
    }
}

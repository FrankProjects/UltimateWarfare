<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use DateTime;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deletes personal data that is past its retention period (GDPR storage limitation).
 * Run daily from cron, e.g.: 0 4 * * * cd /var/www/ultimatewarfare && bin/console game:privacy:cleanup --env=prod
 * Keep the retention periods in sync with the privacy policy.
 */
#[AsCommand(name: 'game:privacy:cleanup', description: 'Delete personal data past its retention period')]
final class PrivacyCleanupCommand extends Command
{
    public const string IP_LOG_RETENTION = '3 months';
    public const string UNVERIFIED_ACCOUNT_RETENTION = '30 days';

    private UserIpLogRepository $userIpLogRepository;
    private UserRepository $userRepository;

    public function __construct(
        UserIpLogRepository $userIpLogRepository,
        UserRepository $userRepository
    ) {
        $this->userIpLogRepository = $userIpLogRepository;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $removedIpLogs = $this->userIpLogRepository->removeLastSeenBefore(
            new DateTime('-' . self::IP_LOG_RETENTION)
        );
        $output->writeln("Removed {$removedIpLogs} IP log rows older than " . self::IP_LOG_RETENTION);

        $removedUsers = $this->userRepository->removeUnverifiedSignedUpBefore(
            new DateTime('-' . self::UNVERIFIED_ACCOUNT_RETENTION)
        );
        $output->writeln(
            "Removed {$removedUsers} unverified accounts older than " . self::UNVERIFIED_ACCOUNT_RETENTION
        );

        return Command::SUCCESS;
    }
}

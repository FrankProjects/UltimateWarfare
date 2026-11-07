<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use DateTimeInterface;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\UserIpLog;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;

/**
 * Collects the personal data stored about a user, for the "Download my data" export (GDPR right of access).
 * Contains the account and the IP log. Game state is left out.
 * Add new personal data here when it is added to the database.
 */
final class UserDataExportService
{
    private UserIpLogRepository $userIpLogRepository;

    public function __construct(UserIpLogRepository $userIpLogRepository)
    {
        $this->userIpLogRepository = $userIpLogRepository;
    }

    /**
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        return [
            'exportedAt' => date(DateTimeInterface::ATOM),
            'account' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'signedUpAt' => $user->getSignedUpAt()->format(DateTimeInterface::ATOM),
            ],
            'ipLog' => array_map(
                static fn (UserIpLog $userIpLog): array => [
                    'ip' => $userIpLog->getIp(),
                    'firstSeenAt' => $userIpLog->getFirstSeenAt()->format(DateTimeInterface::ATOM),
                    'lastSeenAt' => $userIpLog->getLastSeenAt()->format(DateTimeInterface::ATOM),
                ],
                $this->userIpLogRepository->findByUser($user)
            ),
        ];
    }
}

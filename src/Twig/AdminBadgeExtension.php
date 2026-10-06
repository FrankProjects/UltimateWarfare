<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Twig;

use FrankProjects\UltimateWarfare\Repository\ContactRepository;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminBadgeExtension extends AbstractExtension
{
    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly UnbanRequestRepository $unbanRequestRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_contact_count', [$this, 'getContactCount']),
            new TwigFunction('admin_unban_request_count', [$this, 'getUnbanRequestCount']),
        ];
    }

    public function getContactCount(): int
    {
        return $this->contactRepository->count();
    }

    public function getUnbanRequestCount(): int
    {
        return $this->unbanRequestRepository->count();
    }
}

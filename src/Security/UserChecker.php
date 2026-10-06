<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Security;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Only runs on login, not on every request.
 * Banned users are deliberately allowed to log in, so they can submit an unban request;
 * bans are enforced per request by the UserSubscriber.
 */
final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    /**
     * Checked after the password is verified, so an unknown password can't reveal the account status.
     */
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEmailVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Your email address is not verified yet. Please use the link in your registration email.'
            );
        }
    }
}

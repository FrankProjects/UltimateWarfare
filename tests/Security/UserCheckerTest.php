<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Security;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

final class UserCheckerTest extends TestCase
{
    public function testUnverifiedUserCannotLogIn(): void
    {
        $user = new User();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        (new UserChecker())->checkPostAuth($user);
    }

    public function testVerifiedUserCanLogIn(): void
    {
        $user = new User();
        $user->setEmailVerified(true);

        (new UserChecker())->checkPostAuth($user);
        $this->addToAssertionCount(1);
    }

    public function testBannedUserCanLogInToRequestUnban(): void
    {
        $user = new User();
        $user->setEmailVerified(true);
        $user->setBanned(true);

        (new UserChecker())->checkPostAuth($user);
        $this->addToAssertionCount(1);
    }
}

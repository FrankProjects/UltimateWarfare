<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\EventSubscriber;

use FrankProjects\UltimateWarfare\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractUserSubscriber
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    protected function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        $user = $token->getUser();
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }
}

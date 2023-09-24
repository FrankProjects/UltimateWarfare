<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use RuntimeException;
use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;

class BaseForumController extends BaseController
{
    public function getGameUser(): ?User
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return null;
        }

        return $user;
    }

    public function ensureForumEnabled(): void
    {
        if ($this->getParameter('app.uw_forum_enabled') !== true) {
            throw new RuntimeException('Forum is disabled!');
        }
    }
}

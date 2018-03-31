<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\User;

class BaseForumController extends BaseController
{
    /**
     * Get User
     *
     * @return User|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function getUser()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return null;
        }

        return $user;
    }
}

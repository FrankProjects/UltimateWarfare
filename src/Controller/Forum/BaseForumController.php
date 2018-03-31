<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\GameAccount;
use FrankProjects\UltimateWarfare\Entity\User;

class BaseForumController extends BaseController
{
    /**
     * Get GameAccount
     *
     * @return GameAccount|null
     * @throws \Doctrine\ORM\ORMException
     */
    public function getGameAccount()
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            return null;
        }

        return $this->getEm()->getRepository('Game:GameAccount')
            ->findOneBy(['masterId' => $user->getId()]);
    }
}

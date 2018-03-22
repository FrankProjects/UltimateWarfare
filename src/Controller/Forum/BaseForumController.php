<?php

namespace FrankProjects\UltimateWarfare\Controller\Forum;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\GameAccount;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseForumController extends BaseController
{
    /**
     * Get GameAccount
     *
     * @return GameAccount|null
     */
    public function getGameAccount()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            return null;
        }

        $em = $this->getDoctrine()->getManager();
        return $em->getRepository(GameAccount::class)
            ->findOneByMasterId($user->getId());
    }
}

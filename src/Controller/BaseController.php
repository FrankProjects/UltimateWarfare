<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @return EntityManager
     */
    protected function getEm(): EntityManager
    {
        if ($this->em === null) {
            $this->em = $this->getDoctrine()->getManager();
        }

        return $this->em;
    }
}

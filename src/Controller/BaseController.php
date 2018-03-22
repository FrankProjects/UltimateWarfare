<?php

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
        if ($this->em === NULL) {
            $this->em = $this->getDoctrine()->getManager();
        }

        return $this->em;
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserController constructor
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function dashboard(): Response
    {
        $firstDateTime = new \DateTime('-24 hours');
        $lastDateTime = new \DateTime();

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => count($this->userRepository->findAll()),
            'loggedInCount' => count($this->userRepository->findByLastLogin($firstDateTime, $lastDateTime))
        ]);
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class WorldController extends AbstractController
{
    /**
     * @var WorldRepository
     */
    private $worldRepository;

    /**
     * WorldController constructor
     *
     * @param WorldRepository $worldRepository
     */
    public function __construct(
        WorldRepository $worldRepository
    ) {
        $this->worldRepository = $worldRepository;
    }

    /**
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('admin/world/list.html.twig', [
            'worlds' => $this->worldRepository->findAll()
        ]);
    }
}

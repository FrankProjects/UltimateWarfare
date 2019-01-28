<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Form\Admin\GameUnitType;
use FrankProjects\UltimateWarfare\Repository\GameUnitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GameUnitController extends AbstractController
{
    /**
     * @var GameUnitRepository
     */
    private $gameUnitRepository;

    /**
     * GameUnitController constructor
     *
     * @param GameUnitRepository $gameUnitRepository
     */
    public function __construct(
        GameUnitRepository $gameUnitRepository
    ) {
        $this->gameUnitRepository = $gameUnitRepository;
    }

    /**
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('admin/gameunit/list.html.twig', [
            'gameUnits' => $this->gameUnitRepository->findAll()
        ]);
    }

    /**
     * @param Request $request
     * @param int $gameUnitId
     * @return Response
     */
    public function edit(Request $request, int $gameUnitId): Response
    {
        $gameUnit = $this->gameUnitRepository->find($gameUnitId);
        if ($gameUnit === null) {
            $this->addFlash('error', 'GameUnit does not exist');
            return $this->redirectToRoute('Admin/GameUnit/List', [], 302);
        }

        $form = $this->createForm(GameUnitType::class, $gameUnit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameUnitRepository->save($gameUnit);
            $this->addFlash('success', "You successfully edited GameUnit");
            return $this->redirectToRoute('Admin/GameUnit/List', [], 302);
        }

        return $this->render('admin/gameunit/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

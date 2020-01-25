<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Entity\GameNews;
use FrankProjects\UltimateWarfare\Form\Admin\GameNewsType;
use FrankProjects\UltimateWarfare\Repository\GameNewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GameNewsController extends AbstractController
{
    private GameNewsRepository $gameNewsRepository;

    public function __construct(
        GameNewsRepository $gameNewsRepository
    ) {
        $this->gameNewsRepository = $gameNewsRepository;
    }

    public function create(Request $request): Response
    {
        $gameNews = new GameNews();
        $form = $this->createForm(GameNewsType::class, $gameNews);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameNewsRepository->save($gameNews);
            $this->addFlash('success', "You successfully created new game news");
            return $this->redirectToRoute('Admin/GameNews/List', [], 302);
        }

        return $this->render('admin/gamenews/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function list(): Response
    {
        return $this->render('admin/gamenews/list.html.twig', [
            'gameNews' => $this->gameNewsRepository->findAll()
        ]);
    }

    public function edit(Request $request, int $gameNewsId): Response
    {
        $gameNews = $this->gameNewsRepository->find($gameNewsId);
        if ($gameNews === null) {
            $this->addFlash('error', 'GameNews does not exist');
            return $this->redirectToRoute('Admin/GameNews/List', [], 302);
        }

        $form = $this->createForm(GameNewsType::class, $gameNews);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->gameNewsRepository->save($gameNews);
            $this->addFlash('success', "You successfully edited game news");
            return $this->redirectToRoute('Admin/GameNews/List', [], 302);
        }

        return $this->render('admin/gamenews/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function remove(int $gameNewsId): RedirectResponse
    {
        $gameNews = $this->gameNewsRepository->find($gameNewsId);
        if ($gameNews === null) {
            $this->addFlash('error', 'GameNews does not exist');
            return $this->redirectToRoute('Admin/GameNews/List', [], 302);
        }

        $this->gameNewsRepository->remove($gameNews);
        $this->addFlash('success', 'GameNews removed!');
        return $this->redirectToRoute('Admin/GameNews/List', [], 302);
    }
}

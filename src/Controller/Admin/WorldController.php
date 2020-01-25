<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Form\Admin\World\MapConfigurationType;
use FrankProjects\UltimateWarfare\Form\Admin\WorldType;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\Action\WorldActionService;
use FrankProjects\UltimateWarfare\Service\WorldGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class WorldController extends AbstractController
{
    private WorldRepository $worldRepository;
    private WorldActionService $worldActionService;
    private WorldGeneratorService $worldGeneratorService;

    public function __construct(
        WorldRepository $worldRepository,
        WorldActionService $worldActionService,
        WorldGeneratorService $worldGeneratorService
    ) {
        $this->worldRepository = $worldRepository;
        $this->worldActionService = $worldActionService;
        $this->worldGeneratorService = $worldGeneratorService;
    }

    public function create(Request $request): Response
    {
        /**
         * XXX TODO: Fix creating world sectors / regions
         */
        $world = new World();
        $form = $this->createForm(WorldType::class, $world);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->worldRepository->save($world);
            $this->addFlash('success', "You successfully created new world");
            return $this->redirectToRoute('Admin/World/List', [], 302);
        }

        return $this->render('admin/world/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function list(): Response
    {
        return $this->render('admin/world/list.html.twig', [
            'worlds' => $this->worldRepository->findAll()
        ]);
    }

    public function edit(Request $request, int $worldId): Response
    {
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            $this->addFlash('error', 'World does not exist');
            return $this->redirectToRoute('Admin/World/List', [], 302);
        }

        $form = $this->createForm(WorldType::class, $world);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->worldRepository->save($world);
            $this->addFlash('success', "You successfully edited world");
            return $this->redirectToRoute('Admin/World/List', [], 302);
        }

        return $this->render('admin/world/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function remove(int $worldId): RedirectResponse
    {
        try {
            $this->worldActionService->remove($worldId);
            $this->addFlash('success', 'World removed!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Admin/World/List', [], 302);
    }

    public function reset(int $worldId): RedirectResponse
    {
        try {
            $this->worldActionService->reset($worldId);
            $this->addFlash('success', 'World reset!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Admin/World/List', [], 302);
    }

    public function generate(Request $request, int $worldId, int $sector = 0): Response
    {
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            $this->addFlash('error', 'World does not exist');
            return $this->redirectToRoute('Admin/World/List', [], 302);
        }

        $map = [];
        $mapConfiguration = $world->getMapConfiguration();
        $form = $this->createForm(MapConfigurationType::class, $mapConfiguration);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $save = (bool)$form->get('save')->getData();
            $map = $this->worldGeneratorService->generate($world, $save, $sector);
            $this->addFlash('success', 'Generated new map!');
        }

        return $this->render('admin/world/generator.html.twig', [
            'map' => $map,
            'form' => $form->createView(),
        ]);
    }
}

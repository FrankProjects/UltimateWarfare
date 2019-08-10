<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldGeneratorConfiguration;
use FrankProjects\UltimateWarfare\Form\Admin\WorldGeneratorType;
use FrankProjects\UltimateWarfare\Form\Admin\WorldType;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\Action\WorldActionService;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder\WorldImageBuilder;
use FrankProjects\UltimateWarfare\Service\WorldGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class WorldController extends AbstractController
{
    /**
     * @var WorldRepository
     */
    private $worldRepository;

    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var WorldActionService
     */
    private $worldActionService;

    /**
     * @var WorldGeneratorService
     */
    private $worldGeneratorService;

    /**
     * WorldController constructor
     *
     * @param WorldRepository $worldRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param WorldActionService $worldActionService
     * @param WorldGeneratorService $worldGeneratorService
     */
    public function __construct(
        WorldRepository $worldRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldActionService $worldActionService,
        WorldGeneratorService $worldGeneratorService
    ) {
        $this->worldRepository = $worldRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldActionService = $worldActionService;
        $this->worldGeneratorService = $worldGeneratorService;
    }

    /**
     * XXX TODO: Fix creating world sectors / regions
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function create(Request $request): Response
    {
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

    /**
     * @return Response
     */
    public function list(): Response
    {
        return $this->render('admin/world/list.html.twig', [
            'worlds' => $this->worldRepository->findAll()
        ]);
    }

    /**
     * @param Request $request
     * @param int $worldId
     * @return Response
     */
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

    /**
     * @param int $worldId
     * @return RedirectResponse
     */
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

    /**
     * @param int $worldId
     * @return RedirectResponse
     */
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

    /**
     * @param int $worldId
     * @return RedirectResponse
     */
    public function generateImages(int $worldId): RedirectResponse
    {
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            $this->addFlash('error', 'World does not exist');
            return $this->redirectToRoute('Admin/World/List', [], 302);
        }

        try {
            $testGD = get_extension_funcs("gd"); // Grab function list
            if (!$testGD) {
                echo "GD not even installed.";
                exit;
            }

            $worldImageBuilder = new WorldImageBuilder();
            $worldImageBuilder->generateForWorld($world);
            $image = $worldImageBuilder->getImage();

            $world->setImage($image);
            $this->worldRepository->save($world);

            $this->addFlash('success', 'World images generated!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Admin/World/List', [], 302);
    }

    /**
     * @param Request $request
     * @param int $worldId
     * @return Response
     */
    public function generate(Request $request, int $worldId): Response
    {
        $world = $this->worldRepository->find($worldId);
        if ($world === null) {
            $this->addFlash('error', 'World does not exist');
            return $this->redirectToRoute('Admin/World/List', [], 302);
        }

        $map = [];
        $worldGeneratorConfiguration = new WorldGeneratorConfiguration();
        $form = $this->createForm(WorldGeneratorType::class, $worldGeneratorConfiguration);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $save = (bool)$form->get('save')->getData();
            $map = $this->worldGeneratorService->generate($world, $worldGeneratorConfiguration, $save);
            $this->addFlash('success', 'Generated new map!');
        }

        return $this->render('admin/world/generator.html.twig', [
            'map' => $map,
            'form' => $form->createView(),
        ]);
    }
}

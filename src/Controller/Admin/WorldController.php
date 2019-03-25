<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldGeneratorConfiguration;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Form\Admin\WorldGeneratorType;
use FrankProjects\UltimateWarfare\Form\Admin\WorldType;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\Action\WorldActionService;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\PerlinNoiseGenerator;
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
     * @var PerlinNoiseGenerator
     */
    private $worldGenerator;

    /**
     * WorldController constructor
     *
     * @param WorldRepository $worldRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param WorldActionService $worldActionService
     * @param PerlinNoiseGenerator $worldGenerator
     */
    public function __construct(
        WorldRepository $worldRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldActionService $worldActionService,
        PerlinNoiseGenerator $worldGenerator
    ) {
        $this->worldRepository = $worldRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldActionService = $worldActionService;
        $this->worldGenerator = $worldGenerator;
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
            if ($worldGeneratorConfiguration->getSeed() === 0) {
                $worldGeneratorConfiguration->setSeed(intval(microtime(true)));
            }
            $map = $this->worldGenerator->generate($worldGeneratorConfiguration);

            if ($form->get('save')->getData() === true) {
                $this->generateWorldRegions($world, $map, $worldGeneratorConfiguration);
                $this->addFlash('success', 'New map saved!');
                return $this->redirectToRoute('Admin/World/List', [], 302);
            } else {
                $this->addFlash('success', 'Generated new map!');
            }
        }

        return $this->render('admin/world/generator.html.twig', [
            'map' => $map,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param World $world
     * @param array $map
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     */
    private function generateWorldRegions(World $world, array $map, WorldGeneratorConfiguration $worldGeneratorConfiguration): void
    {
        //$gd = imagecreatetruecolor($worldGeneratorConfiguration->getSize(), $worldGeneratorConfiguration->getSize());

        foreach ($map as $x => $yData) {
            foreach ($yData as $y => $z) {
                $z = intval($z * 100);
                $worldRegion = WorldRegion::createForWorld($world, $x, $y, $z, $worldGeneratorConfiguration);
                $this->worldRegionRepository->save($worldRegion);
            }
        }
    }
}

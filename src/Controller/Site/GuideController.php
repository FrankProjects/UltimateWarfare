<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Repository\GameUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\OperationRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRepository;
use Symfony\Component\HttpFoundation\Response;

final class GuideController extends BaseController
{
    /**
     * @return Response
     */
    public function attack(): Response
    {
        return $this->render('site/guide/attack.html.twig');
    }

    /**
     * @return Response
     */
    public function construction(): Response
    {
        return $this->render('site/guide/construction.html.twig');
    }

    /**
     * @param int $gameUnitId
     * @param GameUnitRepository $gameUnitRepository
     * @return Response
     */
    public function gameUnit(int $gameUnitId, GameUnitRepository $gameUnitRepository): Response
    {
        $gameUnit = $gameUnitRepository->find($gameUnitId);

        if ($gameUnit === null) {
            $this->addFlash('error', 'No such game unit!');
            return $this->redirectToRoute('Guide/ListUnits');
        }

        return $this->render('site/guide/gameUnit.html.twig', [
            'gameUnit' => $gameUnit
        ]);
    }

    /**
     * @return Response
     */
    public function federation(): Response
    {
        return $this->render('site/guide/federation.html.twig');
    }

    /**
     * @return Response
     */
    public function fleet(): Response
    {
        return $this->render('site/guide/fleet.html.twig');
    }

    /**
     * @return Response
     */
    public function headquarter(): Response
    {
        return $this->render('site/guide/headquarter.html.twig');
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('site/guide/index.html.twig');
    }

    /**
     * @param OperationRepository $operationRepository
     * @return Response
     */
    public function listOperations(OperationRepository $operationRepository): Response
    {
        $operations = $operationRepository->findAll();

        return $this->render('site/guide/listOperations.html.twig', [
            'operations' => $operations
        ]);
    }

    /**
     * @param ResearchRepository $researchRepository
     * @return Response
     */
    public function listResearch(ResearchRepository $researchRepository): Response
    {
        $researches = $researchRepository->findAll();

        return $this->render('site/guide/listResearch.html.twig', [
            'researches' => $researches
        ]);
    }

    /**
     * @param int $gameUnitTypeId
     * @param GameUnitTypeRepository $gameUnitTypeRepository
     * @return Response
     */
    public function listUnits(int $gameUnitTypeId, GameUnitTypeRepository $gameUnitTypeRepository): Response
    {
        $gameUnitType = $gameUnitTypeRepository->find($gameUnitTypeId);

        if ($gameUnitType === null) {
            $this->addFlash('error', 'No such game unit type!');

            $gameUnitTypes =$gameUnitTypeRepository->findAll();

            return $this->render('site/guide/selectGameUnitType.html.twig', [
                'gameUnitTypes' => $gameUnitTypes
            ]);
        }

        return $this->render('site/guide/listGameUnits.html.twig', [
            'gameUnitType' => $gameUnitType
        ]);
    }

    /**
     * @return Response
     */
    public function logOff(): Response
    {
        return $this->render('site/guide/logOff.html.twig');
    }

    /**
     * @return Response
     */
    public function market(): Response
    {
        return $this->render('site/guide/market.html.twig');
    }

    /**
     * @return Response
     */
    public function mission(): Response
    {
        return $this->render('site/guide/mission.html.twig');
    }

    /**
     * @return Response
     */
    public function ranking(): Response
    {
        return $this->render('site/guide/ranking.html.twig');
    }

    /**
     * @return Response
     */
    public function region(): Response
    {
        return $this->render('site/guide/region.html.twig');
    }

    /**
     * @return Response
     */
    public function report(): Response
    {
        return $this->render('site/guide/report.html.twig');
    }

    /**
     * @return Response
     */
    public function research(): Response
    {
        return $this->render('site/guide/research.html.twig');
    }

    /**
     * @return Response
     */
    public function rules(): Response
    {
        return $this->render('site/guide/rules.html.twig');
    }

    /**
     * @return Response
     */
    public function surrender(): Response
    {
        return $this->render('site/guide/surrender.html.twig');
    }

    /**
     * @return Response
     */
    public function world(): Response
    {
        return $this->render('site/guide/world.html.twig');
    }
}

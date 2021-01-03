<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Repository\GameUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\OperationRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRepository;
use Symfony\Component\HttpFoundation\Response;

final class GuideController extends BaseController
{
    public function attack(): Response
    {
        return $this->render('site/guide/attack.html.twig');
    }

    public function construction(): Response
    {
        return $this->render('site/guide/construction.html.twig');
    }

    public function gameUnit(int $gameUnitId, GameUnitRepository $gameUnitRepository): Response
    {
        $gameUnit = $gameUnitRepository->find($gameUnitId);

        if ($gameUnit === null) {
            $this->addFlash('error', 'No such game unit!');
            return $this->redirectToRoute('Guide/ListUnits');
        }

        return $this->render(
            'site/guide/gameUnit.html.twig',
            [
                'gameUnit' => $gameUnit
            ]
        );
    }

    public function federation(): Response
    {
        return $this->render('site/guide/federation.html.twig');
    }

    public function fleet(): Response
    {
        return $this->render('site/guide/fleet.html.twig');
    }

    public function headquarter(): Response
    {
        return $this->render('site/guide/headquarter.html.twig');
    }

    public function index(): Response
    {
        return $this->render('site/guide/index.html.twig');
    }

    public function listOperations(OperationRepository $operationRepository): Response
    {
        $operations = $operationRepository->findAll();

        return $this->render(
            'site/guide/listOperations.html.twig',
            [
                'operations' => $operations
            ]
        );
    }

    public function listResearch(ResearchRepository $researchRepository): Response
    {
        $researches = $researchRepository->findAll();

        return $this->render(
            'site/guide/listResearch.html.twig',
            [
                'researches' => $researches
            ]
        );
    }

    public function listUnits(int $gameUnitTypeId, GameUnitTypeRepository $gameUnitTypeRepository): Response
    {
        $gameUnitType = $gameUnitTypeRepository->find($gameUnitTypeId);

        if ($gameUnitType === null) {
            $gameUnitTypes = $gameUnitTypeRepository->findAll();

            return $this->render(
                'site/guide/selectGameUnitType.html.twig',
                [
                    'gameUnitTypes' => $gameUnitTypes
                ]
            );
        }

        return $this->render(
            'site/guide/listGameUnits.html.twig',
            [
                'gameUnitType' => $gameUnitType
            ]
        );
    }

    public function logOff(): Response
    {
        return $this->render('site/guide/logOff.html.twig');
    }

    public function market(): Response
    {
        return $this->render('site/guide/market.html.twig');
    }

    public function ranking(): Response
    {
        return $this->render('site/guide/ranking.html.twig');
    }

    public function region(): Response
    {
        return $this->render('site/guide/region.html.twig');
    }

    public function report(): Response
    {
        return $this->render('site/guide/report.html.twig');
    }

    public function research(): Response
    {
        return $this->render('site/guide/research.html.twig');
    }

    public function rules(): Response
    {
        return $this->render('site/guide/rules.html.twig');
    }

    public function surrender(): Response
    {
        return $this->render('site/guide/surrender.html.twig');
    }

    public function world(): Response
    {
        return $this->render('site/guide/world.html.twig');
    }
}

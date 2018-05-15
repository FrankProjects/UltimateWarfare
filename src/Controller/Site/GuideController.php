<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GuideController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function attack(Request $request): Response
    {
        return $this->render('site/guide/attack.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function construction(Request $request): Response
    {
        return $this->render('site/guide/construction.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function gameUnit(Request $request, int $gameUnitId): Response
    {
        $em = $this->getEm();
        $gameUnit = $em->getRepository('Game:GameUnit')
            ->find($gameUnitId);

        if ($gameUnit === null) {
            $this->addFlash('error', 'No such game unit!');
            return $this->redirectToRoute('Guide/ListUnits');
        }

        return $this->render('site/guide/gameUnit.html.twig', [
            'gameUnit' => $gameUnit
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function federation(Request $request): Response
    {
        return $this->render('site/guide/federation.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function fleet(Request $request): Response
    {
        return $this->render('site/guide/fleet.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function headquarter(Request $request): Response
    {
        return $this->render('site/guide/headquarter.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        return $this->render('site/guide/index.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listOperations(Request $request): Response
    {
        return $this->render('site/guide/listOperations.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listResearch(Request $request): Response
    {
        $em = $this->getEm();
        $researches = $em->getRepository('Game:Research')
            ->findAll();


        return $this->render('site/guide/listResearch.html.twig', [
            'researches' => $researches
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listUnits(Request $request, int $gameUnitTypeId): Response
    {
        $em = $this->getEm();
        $gameUnitType = $em->getRepository('Game:GameUnitType')
            ->find($gameUnitTypeId);

        if ($gameUnitType === null) {
            $this->addFlash('error', 'No such game unit type!');

            $gameUnitTypes = $em->getRepository('Game:GameUnitType')
                ->findAll();
            return $this->render('site/guide/selectGameUnitType.html.twig', [
                'gameUnitTypes' => $gameUnitTypes
            ]);
        }

        return $this->render('site/guide/listGameUnits.html.twig', [
            'gameUnitType' => $gameUnitType
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function logOff(Request $request): Response
    {
        return $this->render('site/guide/logOff.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function market(Request $request): Response
    {
        return $this->render('site/guide/market.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function mission(Request $request): Response
    {
        return $this->render('site/guide/mission.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function ranking(Request $request): Response
    {
        return $this->render('site/guide/ranking.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function region(Request $request): Response
    {
        return $this->render('site/guide/region.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function report(Request $request): Response
    {
        return $this->render('site/guide/report.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function research(Request $request): Response
    {
        return $this->render('site/guide/research.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function rules(Request $request): Response
    {
        return $this->render('site/guide/rules.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function surrender(Request $request): Response
    {
        return $this->render('site/guide/surrender.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function world(Request $request): Response
    {
        return $this->render('site/guide/world.html.twig');
    }
}

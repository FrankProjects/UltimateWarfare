<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GuideController extends Controller
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
        return $this->render('site/guide/listResearch.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listUnits(Request $request): Response
    {
        return $this->render('site/guide/listUnits.html.twig');
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

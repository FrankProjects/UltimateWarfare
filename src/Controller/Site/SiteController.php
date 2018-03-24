<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SiteController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function about(Request $request): Response
    {
        return $this->render('site/about.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function advertise(Request $request): Response
    {
        return $this->render('site/advertise.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function community(Request $request): Response
    {
        return $this->render('site/community.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function donate(Request $request): Response
    {
        return $this->render('site/donate.html.twig');
    }

    /**
     * XXX TODO: Fix me
     *
     * @param Request $request
     * @return Response
     */
    public function statistics(Request $request): Response
    {
        return $this->render('site/statistics.html.twig', [
            'lastLogin' => '',
            'lastRegistered' => '',
            'totalUsers' => ''
        ]);
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

final class SiteController extends BaseController
{
    /**
     * @return Response
     */
    public function about(): Response
    {
        return $this->render('site/about.html.twig');
    }

    /**
     * @return Response
     */
    public function advertise(): Response
    {
        return $this->render('site/advertise.html.twig');
    }

    /**
     * @return Response
     */
    public function community(): Response
    {
        return $this->render('site/community.html.twig');
    }

    /**
     * @return Response
     */
    public function donate(): Response
    {
        return $this->render('site/donate.html.twig');
    }
}

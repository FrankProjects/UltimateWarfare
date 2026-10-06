<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;

final class IndexController extends BaseController
{
    public function index(): Response
    {
        return $this->render('site/index.html.twig');
    }
}

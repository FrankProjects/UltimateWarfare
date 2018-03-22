<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContactController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function contact(Request $request): Response
    {
        return $this->render('site/contact.html.twig');
    }
}

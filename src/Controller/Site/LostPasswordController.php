<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class LostPasswordController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function lostPassword(Request $request): Response
    {
        return $this->render('site/lostPassword.html.twig', [
            'token' => 'test'
        ]);
    }
}

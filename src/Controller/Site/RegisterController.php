<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Form\RegistrationType;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Service\Action\RegisterActionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RegisterController extends AbstractController
{
    private RegisterActionService $registerActionService;

    public function __construct(
        RegisterActionService $registerActionService
    ) {
        $this->registerActionService = $registerActionService;
    }

    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->registerActionService->register($user);
                $this->addFlash(
                    'success',
                    "You successfully created an account! An e-mail has been sent to {$user->getEmail()} with your activation code..."
                );
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'site/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function activateUser(string $token): Response
    {
        try {
            $this->registerActionService->activateUser($token);
            $this->addFlash('success', 'You successfully activated your account!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Site/Login');
    }
}

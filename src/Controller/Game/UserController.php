<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Form\ChangePasswordType;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserController extends BaseGameController
{
    private UserRepository $userRepository;
    private UnbanRequestRepository $unbanRequestRepository;

    public function __construct(
        UserRepository $userRepository,
        UnbanRequestRepository $unbanRequestRepository
    ) {
        $this->userRepository = $userRepository;
        $this->unbanRequestRepository = $unbanRequestRepository;
    }

    public function account(): Response
    {
        return $this->render(
            'game/account.html.twig',
            [
                'user' => $this->getGameUser()
            ]
        );
    }

    public function banned(Request $request): Response
    {
        $user = $this->getGameUser(false);
        if ($user->getActive()) {
            $this->addFlash('error', 'You are not banned!');
            return $this->redirectToRoute('Game/Account');
        }

        $unbanRequest = $this->unbanRequestRepository->findByUser($user);

        if ($unbanRequest === null) {
            $unbanRequest = new UnbanRequest();
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $unbanReason = trim($request->request->get('post'));

            $unbanRequest->setPost($unbanReason);
            $unbanRequest->setUser($user);
            $this->unbanRequestRepository->save($unbanRequest);

            $this->addFlash('success', 'We have received your request, we will try to read your request ASAP...');
        }

        return $this->render(
            'game/banned.html.twig',
            [
                'user' => $user,
                'unbanRequest' => $unbanRequest
            ]
        );
    }

    public function edit(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->getGameUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);

        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $oldPassword = $changePasswordForm->get('oldPassword')->getData();
            $plainPassword = $changePasswordForm->get('plainPassword')->getData();

            if ($encoder->isPasswordValid($user, $oldPassword)) {
                if ($plainPassword === null) {
                    $this->addFlash('error', 'New passwords do not match');
                } else {
                    $newEncodedPassword = $encoder->encodePassword($user, $plainPassword);
                    $user->setPassword($newEncodedPassword);
                    $this->userRepository->save($user);

                    $this->addFlash('success', "Password change successfully!");
                }
            } else {
                $this->addFlash('error', 'Old password is invalid');
            }
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->request->get('change_settings')) {
                $this->changeSettings($request);
            }
        }

        return $this->render(
            'game/editAccount.html.twig',
            [
                'user' => $this->getGameUser(),
                'userType' => $this->getAccountType(),
                'changePasswordForm' => $changePasswordForm->createView()
            ]
        );
    }

    private function getAccountType(): string
    {
        $user = $this->getGameUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_PLAYER', $roles)) {
            return 'Player';
        }

        if (in_array('ROLE_ADMIN', $roles)) {
            return 'Admin';
        }

        return 'Guest';
    }

    private function changeSettings(Request $request)
    {
        $user = $this->getGameUser();

        if ($request->request->get('adviser')) {
            if ($user->getAdviser() == 0) {
                $user->setAdviser(true);
                $this->userRepository->save($user);

                $this->addFlash('success', 'Successfully changed settings!');
            }
        } else {
            if ($user->getAdviser() == 1) {
                $user->setAdviser(false);
                $this->userRepository->save($user);

                $this->addFlash('success', 'Successfully changed settings!');
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Form\ChangePasswordType;
use FrankProjects\UltimateWarfare\Form\UploadAvatarType;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
            $unbanReason = trim((string) $request->request->get('post'));

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

    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getGameUser();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $user);
        $changePasswordForm->handleRequest($request);

        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $oldPassword = $changePasswordForm->get('oldPassword')->getData();
            $plainPassword = $changePasswordForm->get('plainPassword')->getData();

            if ($passwordHasher->isPasswordValid($user, $oldPassword)) {
                if ($plainPassword === null) {
                    $this->addFlash('error', 'New passwords do not match');
                } else {
                    $newEncodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($newEncodedPassword);
                    $this->userRepository->save($user);

                    $this->addFlash('success', "Password change successfully!");
                }
            } else {
                $this->addFlash('error', 'Old password is invalid');
            }
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            if ($request->request->get('change_settings') !== null) {
                $this->changeSettings($request);
            }
        }

        return $this->render(
            'game/editAccount.html.twig',
            [
                'user' => $this->getGameUser(),
                'userType' => $this->getAccountType(),
                'changePasswordForm' => $changePasswordForm->createView(),
                'uploadAvatar' => $this->createForm(UploadAvatarType::class)->createView()
            ]
        );
    }

    public function uploadAvatar(Request $request): Response
    {
        $user = $this->getGameUser();
        $form = $this->createForm(UploadAvatarType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatar = $form->get('avatar')->getData();

            if ($avatar !== null) {
                $uploadedFile = $user->getId() . '-' . uniqid('', true) . '.' . $avatar->guessExtension();

                try {
                    $avatar->move(
                        $this->getParameter('app.avatars_directory'),
                        $uploadedFile
                    );

                    $pngFileName = $user->getId() . '-' . uniqid('', true) . '.png';

                    $image = new \Imagick($this->getParameter('app.avatars_directory') . '/' . $uploadedFile);
                    $image->setImageFormat('png');
                    $image->setImageBackgroundColor('transparent');
                    $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_OPAQUE);
                    $image->cropThumbnailImage(200, 200);
                    $image->roundCornersImage(100, 100);
                    $image->writeImage($this->getParameter('app.avatars_directory') . '/' . $pngFileName);

                    // Delete existing avatar
                    if ($user->getAvatar() != '' && file_exists($this->getParameter('app.avatars_directory') . '/' . $user->getAvatar())) {
                        unlink($this->getParameter('app.avatars_directory') . '/' . $user->getAvatar());
                    }

                    unlink($this->getParameter('app.avatars_directory') . '/' . $uploadedFile);

                    $user->setAvatar($pngFileName);
                    $this->userRepository->save($user);

                    $this->addFlash('success', 'Avatar uploaded successfully!');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Could not upload avatar');
                }

                return $this->redirectToRoute('Game/Account/Edit');
            }
        }

        return $this->render(
            'game/editAccount.html.twig',
            [
                'user' => $this->getGameUser(),
                'userType' => $this->getAccountType(),
                'changePasswordForm' => $this->createForm(ChangePasswordType::class)->createView(),
                'uploadAvatar' => $form->createView()
            ]
        );
    }

    private function getAccountType(): string
    {
        $user = $this->getGameUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_PLAYER', $roles, true)) {
            return 'Player';
        }

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return 'Admin';
        }

        return 'Guest';
    }

    private function changeSettings(Request $request): void
    {
        $user = $this->getGameUser();

        if ($request->request->get('adviser') !== null) {
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

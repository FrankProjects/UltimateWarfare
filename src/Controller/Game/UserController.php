<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\MapDesign;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function account(Request $request): Response
    {
        return $this->render('game/account.html.twig', [
            'user' => $this->getGameUser()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function banned(Request $request): Response
    {
        $user = $this->getGameUser();
        if ($user->getActive()) {
            $this->addFlash('error', 'You are not banned!');
            return $this->redirectToRoute('Game/Account');
        }

        $em = $this->getEm();
        $unbanRequest = $em->getRepository('Game:UnbanRequest')
            ->findOneBy(['user' => $user]);

        if ($unbanRequest === null) {
            $unbanRequest = new UnbanRequest();
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getEm();
            $unbanReason = trim($request->request->get('post'));

            $unbanRequest->setPost($unbanReason);
            $unbanRequest->setUser($user);
            $em->persist($unbanRequest);
            $em->flush();

            $this->addFlash('success', 'We have received your request, we will try to read your request ASAP...');
        }

        return $this->render('game/banned.html.twig', [
            'user' => $this->getGameUser(),
            'unbanRequest' => $unbanRequest
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @throws \Exception
     */
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
                    $em = $this->getEm();
                    $em->persist($user);

                    $em->flush();
                    $this->addFlash('success', "Password change successfully!");
                }
            } else {
                $this->addFlash('error', 'Old password is invalid');
            }
        }

        if ($request->getMethod() == 'POST') {
            if ($request->request->get('change_map') && $request->request->get('map')) {
                $this->changeMapDesign($request);
            }

            if ($request->request->get('change_settings')) {
                $this->changeSettings($request);
            }
        }

        return $this->render('game/editAccount.html.twig', [
            'user' => $this->getGameUser(),
            'mapDesigns' => $this->getAllMapDesigns(),
            'userType' => $this->getAccountType(),
            'changePasswordForm' => $changePasswordForm->createView()
        ]);
    }

    /**
     * Get all MapDesigns
     *
     * @return MapDesign[]
     */
    private function getAllMapDesigns(): array
    {
        $em = $this->getEm();
        $repository = $em->getRepository('Game:MapDesign');
        return $repository->findAll();
    }

    /**
     * @return string
     */
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

    /**
     * Change map design
     *
     * @param Request $request
     * @throws \Exception
     */
    private function changeMapDesign(Request $request)
    {
        $user = $this->getGameUser();
        $em = $this->getEm();
        $mapDesign = $em->getRepository('Game:MapDesign')
            ->find($request->request->get('map'));

        if(!$mapDesign) {
            $this->addFlash('error', 'No such map design');
        } else if ($mapDesign->getId() != $user->getMapDesign()->getId()){
            $user->setMapDesign($mapDesign);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Map design succesfully changed!');
        }
    }

    /**
     * Change settings
     *
     * @param Request $request
     * @throws \Exception
     */
    private function changeSettings(Request $request)
    {
        $user = $this->getGameUser();
        $em = $this->getEm();

        if ($request->request->get('adviser')) {
            if ($user->getAdviser() == 0) {
                $user->setAdviser(true);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Succesfully changed settings!');
            }
        } else {
            if ($user->getAdviser() == 1) {
                $user->setAdviser(false);
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Succesfully changed settings!');
            }
        }
    }
}

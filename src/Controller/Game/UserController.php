<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\MapDesign;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UserController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function account(Request $request): Response
    {
        return $this->render('game/account.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function banned(Request $request): Response
    {
        $user = $this->getUser();
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

            $this->addFlash('success', 'We have recieved your request, we will try to read your request ASAP...');
        }

        return $this->render('game/banned.html.twig', [
            'user' => $this->getUser(),
            'unbanRequest' => $unbanRequest
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request): Response
    {
        if ($request->getMethod() == 'POST') {
            if ($request->request->get('change_map') && $request->request->get('map')) {
                $this->changeMapDesign($request);
            }

            if ($request->request->get('change_settings')) {
                $this->changeSettings($request);
            }
        }

        return $this->render('game/editAccount.html.twig', [
            'user' => $this->getUser(),
            'mapDesigns' => $this->getAllMapDesigns(),
            'userType' => $this->getAccountType(),
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
        $user = $this->getUser();
        switch ($user->getRoles()):
            case 1:
                $accountType = "Player";
                break;

            case 2:
                $accountType = "VIP Player";
                break;

            case 3:
                $accountType = "Forum Moderator";
                break;

            case 5:
                $accountType = "Moderator";
                break;

            case 10:
                $accountType = "Admin";
                break;

            default:
                $accountType = "Guest";
        endswitch;

        return $accountType;
    }

    /**
     * Change map design
     *
     * @param Request $request
     */
    private function changeMapDesign(Request $request)
    {
        $user = $this->getUser();
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
     */
    private function changeSettings(Request $request)
    {
        $user = $this->getUser();
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

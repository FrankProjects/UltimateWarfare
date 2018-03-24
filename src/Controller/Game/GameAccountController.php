<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Banned;
use FrankProjects\UltimateWarfare\Entity\MapDesign;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GameAccountController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function account(Request $request): Response
    {
        return $this->render('game/account.html.twig', [
            'user' => $this->getUser(),
            'gameAccount' => $this->getGameAccount()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function banned(Request $request): Response
    {
        $gameAccount = $this->getGameAccount();
        if ($gameAccount->getActive()) {
            $request->getSession()->getFlashBag()->add('error', 'You are not banned!');
            return $this->redirectToRoute('Game/Account');
        }

        $em = $this->getEm();
        $unbanRequest = $em->getRepository('Game:UnbanRequest')
            ->findOneBy(['gameAccount' => $gameAccount]);

        if ($unbanRequest === null) {
            $unbanRequest = new UnbanRequest();
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getEm();
            $unbanReason = trim($request->request->get('post'));

            $unbanRequest->setPost($unbanReason);
            $unbanRequest->setGameAccount($gameAccount);
            $em->persist($unbanRequest);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'We have recieved your request, we will try to read your request ASAP...');
        }

        return $this->render('game/banned.html.twig', [
            'gameAccount' => $this->getGameAccount(),
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

            if ($request->request->get('change_forumname')) {
                $this->changeForumName($request);
            }

            if ($request->request->get('change_settings')) {
                $this->changeSettings($request);
            }
        }

        return $this->render('game/editAccount.html.twig', [
            'user' => $this->getUser(),
            'gameAccount' => $this->getGameAccount(),
            'mapDesigns' => $this->getAllMapDesigns(),
            'gameAccountType' => $this->getAccountType(),
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
        $gameAccount = $this->getGameAccount();
        switch ($gameAccount->getLevel()):
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
        $gameAccount = $this->getGameAccount();
        $em = $this->getEm();
        $mapDesign = $em->getRepository('Game:MapDesign')
            ->find($request->request->get('map'));

        if(!$mapDesign) {
            $request->getSession()->getFlashBag()->add('error', 'No such map design');
        } else if ($mapDesign->getId() != $gameAccount->getMapDesign()->getId()){
            $gameAccount->setMapDesign($mapDesign);
            $em->persist($gameAccount);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Map design succesfully changed!');
        }
    }

    /**
     * Change forum name
     *
     * @param Request $request
     */
    private function changeForumName(Request $request)
    {
        $forumName = trim($request->request->get('name'));
        if (empty($forumName)){
            $request->getSession()->getFlashBag()->add('error', 'Please enter a forum name');
            return;
        }

        if(preg_match("/^[0-9a-zA-Z_]{3,15}$/", $forumName) === 0){
            $request->getSession()->getFlashBag()->add('error', 'Your Forum name may only contain letters, digits, underscores ( _ ) and the username should be between 3 and 15 characters!');
            return;
        }

        $em = $this->getEm();
        $gameAccount = $em->getRepository('Game:GameAccount')
            ->findBy(['forumName' => $forumName]);

        if ($gameAccount) {
            $request->getSession()->getFlashBag()->add('error', 'This forumname already excist!');
            return;
        }

        $gameAccount = $this->getGameAccount();
        if ($gameAccount->getForumName() != $forumName) {
            $gameAccount->setForumName($forumName);
            $em->persist($gameAccount);
            $em->flush();
            $request->getSession()->getFlashBag()->add('success', 'Forum name succesfully changed!');
        }
    }

    /**
     * Change settings
     *
     * @param Request $request
     */
    private function changeSettings(Request $request)
    {
        $gameAccount = $this->getGameAccount();
        $em = $this->getEm();

        if ($request->request->get('adviser')) {
            if ($gameAccount->getAdviser() == 0) {
                $gameAccount->setAdviser(true);
                $em->persist($gameAccount);
                $em->flush();
                $request->getSession()->getFlashBag()->add('success', 'Succesfully changed settings!');
            }
        } else {
            if ($gameAccount->getAdviser() == 1) {
                $gameAccount->setAdviser(false);
                $em->persist($gameAccount);
                $em->flush();
                $request->getSession()->getFlashBag()->add('success', 'Succesfully changed settings!');
            }
        }
    }
}

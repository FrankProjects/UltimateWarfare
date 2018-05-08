<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Form\ConfirmPasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class SurrenderController extends BaseGameController
{
    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @throws /Exception
     */
    public function surrender(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $player = $this->getPlayer();
        $canSurrender = false;
        if ($player->getTimestampJoined() + 3600 * 24 * 2 < time()) {
            $canSurrender = true;
        }

        $user = $this->getGameUser();
        $confirmPasswordForm = $this->createForm(ConfirmPasswordType::class, $user);
        $confirmPasswordForm->handleRequest($request);

        if ($confirmPasswordForm->isSubmitted() && $confirmPasswordForm->isValid()) {
            if ($this->handleSurrender($confirmPasswordForm, $user, $encoder)) {
                return $this->redirectToRoute('Game/Account');
            }
        }

        return $this->render('game/surrender.html.twig', [
            'player' => $this->getPlayer(),
            'canSurrender' => $canSurrender,
            'confirmPasswordForm' => $confirmPasswordForm->createView()
        ]);
    }

    /**
     * @param FormInterface $confirmPasswordForm
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     * @throws \Exception
     * @return bool
     */
    private function handleSurrender(FormInterface $confirmPasswordForm, User $user, UserPasswordEncoderInterface $encoder)
    {
        $plainPassword = $confirmPasswordForm->get('plainPassword')->getData();

        if ($encoder->isPasswordValid($user, $plainPassword)) {
            $em = $this->getEm();

            $player = $this->getPlayer();
            foreach ($player->getMarketItems() as $marketItem) {
                $em->remove($marketItem);
            }

            $federation = $player->getFederation();
            if ($federation !== null) {
                // XXX TODO: Delete Federation if you are owner
                $federation->setNetworth($federation->getNetworth() - $player->getNetworth());
                $federation->setRegions($federation->getRegions() - $player->getRegions());
                $em->persist($federation);
            }


            foreach ($player->getConstructions() as $construction) {
                $em->remove($construction);
            }

            foreach ($player->getReports() as $report) {
                $em->remove($report);
            }

            foreach ($player->getFederationApplications() as $federationApplication) {
                $em->remove($federationApplication);
            }

            foreach ($player->getFleets() as $fleet) {
                $em->remove($fleet);
            }

            foreach ($player->getPlayerResearch() as $playerResearch) {
                $em->remove($playerResearch);
            }

            foreach ($player->getWorldRegions() as $worldRegion) {
                foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                    $em->remove($worldRegionUnit);
                }

                $worldRegion->setName('');
                $worldRegion->setPlayer(null);
                $em->persist($worldRegion);
            }

            // XXX TODO: Do we want to keep messages so other players don't lose messages from their in/outbox?
            foreach ($player->getFromMessages() as $message) {
                $em->remove($message);
            }

            foreach ($player->getToMessages() as $message) {
                $em->remove($message);
            }

            $em->remove($player);
            $em->flush();
            $this->addFlash('success', "You have surrendered your empire...");
            return true;
        } else {
            $this->addFlash('error', 'Wrong password!');
        }

        return false;
    }
}

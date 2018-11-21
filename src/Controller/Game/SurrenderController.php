<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Form\ConfirmPasswordType;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Throwable;

final class SurrenderController extends BaseGameController
{
    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param PlayerRepository $playerRepository
     * @return Response
     */
    public function surrender(Request $request, UserPasswordEncoderInterface $encoder, PlayerRepository $playerRepository): Response
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
            $plainPassword = $confirmPasswordForm->get('plainPassword')->getData();

            if ($encoder->isPasswordValid($user, $plainPassword)) {
                try {
                    $playerRepository->remove($player);
                    $this->addFlash('success', "You have surrendered your empire...");
                    return $this->redirectToRoute('Game/Account');

                } catch (Throwable $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Wrong password!');
            }
        }

        return $this->render('game/surrender.html.twig', [
            'player' => $this->getPlayer(),
            'canSurrender' => $canSurrender,
            'confirmPasswordForm' => $confirmPasswordForm->createView()
        ]);
    }
}

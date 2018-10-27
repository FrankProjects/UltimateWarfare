<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Form\Game\NotepadType;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NotepadController extends BaseGameController
{
    /**
     * @param Request $request
     * @param PlayerRepository $playerRepository
     * @return Response
     */
    public function notepad(Request $request, PlayerRepository $playerRepository): Response
    {
        $player = $this->getPlayer();
        $notepadForm = $this->createForm(NotepadType::class, $player);
        $notepadForm->handleRequest($request);

        if ($notepadForm->isSubmitted() && $notepadForm->isValid()) {
            $playerRepository->save($player);
            $this->addFlash('success', 'Notepad saved!');
        }

        return $this->render('game/notepad.html.twig', [
            'player' => $player,
            'form' => $notepadForm->createView()
        ]);
    }
}

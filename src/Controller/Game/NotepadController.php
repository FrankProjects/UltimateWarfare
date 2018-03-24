<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class NotepadController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function notepad(Request $request): Response
    {
        $player = $this->getPlayer();
        if ($request->getMethod() == 'POST') {
            $em = $this->getEm();
            $notepad = trim($request->request->get('message'));
            $player->setNotepad($notepad);
            $em->persist($player);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Notepad saved!');
        }
        return $this->render('game/notepad.html.twig', [
            'player' => $player,
        ]);
    }
}

<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResearchController extends BaseGameController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function research(Request $request): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $researchRepository = $em->getRepository('Game:Research');
        $ongoingResearch = $researchRepository->findOngoingByPlayer($player);
        $unresearched = $researchRepository->findUnresearchedByPlayer($player);

        $researchPlayerRecords = $em->getRepository('Game:ResearchPlayer')
            ->findBy(['player' => $player]);

        $researchPlayerArray = [];
        foreach ($researchPlayerRecords as $researchPlayer) {
            $researchPlayerArray[] = $researchPlayer->getResearch()->getId();
        }

        // XXX TODO: Make better code
        /** @var Research $notResearched */
        foreach ($unresearched as $key => $notResearched) {
            $unresearched[$key]->needs['done'] = [];
            $unresearched[$key]->needs['notDone'] = [];
            foreach ($notResearched->getResearchNeeds() as $researchNeed) {
                if (in_array($researchNeed->getRequiredResearch()->getId(), $researchPlayerArray)) {
                    $unresearched[$key]->needs['done'][] = $researchNeed;
                } else {
                    $unresearched[$key]->needs['notDone'][] = $researchNeed;
                }
            }
        }

        return $this->render('game/research.html.twig', [
            'player' => $player,
            'ongoingResearch' => $ongoingResearch,
            'unresearched' => $unresearched
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function history(Request $request): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $finishedResearch = $em->getRepository('Game:Research')
            ->findFinishedByPlayer($player);

        return $this->render('game/researchHistory.html.twig', [
            'player' => $player,
            'finishedResearch' => $finishedResearch
        ]);
    }

    /**
     * @param Request $request
     * @param int $researchId
     * @return Response
     */
    public function performResearch(Request $request, int $researchId): Response
    {
        $player = $this->getPlayer();
        $em = $this->getEm();
        $research = $em->getRepository('Game:Research')
            ->find($researchId);

        if ($research === null) {
            $this->addFlash('error', 'This technology does\'t excist!');
            return $this->redirectToRoute('Game/Research');
        }

        if (!$research->getActive()) {
            $this->addFlash('error', 'This technology is disabled!');
            return $this->redirectToRoute('Game/Research');
        }

        $researching = $em->getRepository('Game:ResearchPlayer')
            ->findBy(['player' => $this->getPlayer(), 'active' => 0]);
        if (count($researching) > 0) {
            $this->addFlash('error', 'You can only research 1 technology at a time!');
            return $this->redirectToRoute('Game/Research');
        }

        $isResearched = $em->getRepository('Game:ResearchPlayer')
            ->findBy(['player' => $player, 'research' => $research]);

        if (count($isResearched) != 0) {
            $this->addFlash('error', 'This technology has already been researched!');
            return $this->redirectToRoute('Game/Research');
        }

        foreach ($research->getResearchNeeds() as $researchNeed) {
            $isResearched = $em->getRepository('Game:ResearchPlayer')
                ->findBy(['player' => $player, 'research' => $researchNeed->getRequiredResearch()]);

            if (count($isResearched) == 0) {
                $this->addFlash('error', 'You don\'t have all required technologies!');
                return $this->redirectToRoute('Game/Research');
            }
        }

        if ($research->getCost() > $player->getCash()) {
            $this->addFlash('error', 'You can\'t afford that!');
            return $this->redirectToRoute('Game/Research');
        }

        $researchPlayer = new ResearchPlayer();
        $researchPlayer->setPlayer($player);
        $researchPlayer->setResearch($research);
        $researchPlayer->setTimestamp(time());

        $player->setCash($player->getCash() - $research->getCost());
        $em->persist($player);
        $em->persist($researchPlayer);
        $em->flush();

        $this->addFlash('success', "You started to research {$research->getName()}");
        return $this->redirectToRoute('Game/Research');

    }

    /**
     * @param Request $request
     * @param int $researchId
     * @return Response
     */
    public function performCancel(Request $request, int $researchId): Response
    {
        $em = $this->getEm();
        $research = $em->getRepository('Game:Research')
            ->find($researchId);

        if ($research === null) {
            $this->addFlash('error', 'This technology does\'t excist!');
            return $this->redirectToRoute('Game/Research');
        }

        if (!$research->getActive()) {
            $this->addFlash('error', 'This technology is disabled!');
            return $this->redirectToRoute('Game/Research');
        }

        $researching = $em->getRepository('Game:ResearchPlayer')
            ->findOneBy(['player' => $this->getPlayer(), 'research' => $research, 'active' => 0]);
        if (!$researching) {
            $this->addFlash('error', 'You are not researching this!');
            return $this->redirectToRoute('Game/Research');
        }

        $em->remove($researching);
        $em->flush();

        $this->addFlash('success', 'Succesfully cancelled your research project!');
        return $this->redirectToRoute('Game/Research');
    }
}

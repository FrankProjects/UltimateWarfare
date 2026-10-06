<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitCategory;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Research\ResearchTierResearch;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use FrankProjects\UltimateWarfare\Repository\OperationRegistry;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use Symfony\Component\HttpFoundation\Response;

final class GuideController extends BaseController
{
    public function attack(): Response
    {
        return $this->render('site/guide/attack.html.twig');
    }

    public function construction(): Response
    {
        return $this->render('site/guide/construction.html.twig');
    }

    public function gameUnit(int $gameUnitId, GameUnitRegistry $gameUnitRegistry): Response
    {
        $gameUnitEnum = GameUnitEnum::tryFrom($gameUnitId);
        if ($gameUnitEnum === null) {
            $this->addFlash('error', 'No such game unit!');
            return $this->redirectToRoute('Guide/ListUnits');
        }

        $gameUnit = $gameUnitRegistry->find($gameUnitEnum);

        return $this->render(
            'site/guide/gameUnit.html.twig',
            [
                'gameUnit' => $gameUnit
            ]
        );
    }

    public function federation(): Response
    {
        return $this->render('site/guide/federation.html.twig');
    }

    public function fleet(): Response
    {
        return $this->render('site/guide/fleet.html.twig');
    }

    public function gettingStarted(): Response
    {
        return $this->render('site/guide/gettingStarted.html.twig');
    }


    public function listOperations(OperationRegistry $operationRegistry, GameUnitRegistry $gameUnitRegistry): Response
    {
        $operations = $operationRegistry->findEnabled();

        $gameUnitNames = [];
        foreach ($operations as $operation) {
            $gameUnit = $operation->getGameUnit();
            if ($gameUnit !== null && !isset($gameUnitNames[$gameUnit->value])) {
                $gameUnitNames[$gameUnit->value] = $gameUnitRegistry->find($gameUnit)->getName();
            }
        }

        return $this->render(
            'site/guide/listOperations.html.twig',
            [
                'operations' => $operations,
                'gameUnitNames' => $gameUnitNames,
            ]
        );
    }

    public function listResearch(ResearchRegistry $researchRegistry): Response
    {
        $researches = $researchRegistry->findEnabled();

        $researchTier = null;
        $researchesByTier = [];
        foreach ($researches as $research) {
            if ($research instanceof ResearchTierResearch) {
                $researchTier = $research;
                continue;
            }
            $tier = 0;
            for ($level = 1; $level <= $research->getMaxLevel(); $level++) {
                foreach ($research->getPrerequisites($level) as $prerequisiteClass => $minLevel) {
                    if ($prerequisiteClass === ResearchTierResearch::class) {
                        $tier = max($tier, $minLevel);
                    }
                }
            }
            $researchesByTier[$tier][] = $research;
        }
        ksort($researchesByTier);
        foreach ($researchesByTier as &$bucket) {
            usort($bucket, static fn ($a, $b) => strcmp($a->getName(), $b->getName()));
        }
        unset($bucket);

        return $this->render(
            'site/guide/listResearch.html.twig',
            [
                'researchTier' => $researchTier,
                'researchesByTier' => $researchesByTier,
            ]
        );
    }

    public function listUnits(int $gameUnitCategoryId, GameUnitRegistry $gameUnitRegistry): Response
    {
        $gameUnitCategory = GameUnitCategory::fromInteger($gameUnitCategoryId);
        if ($gameUnitCategory === null) {
            $gameUnitCategory = GameUnitCategory::BUILDINGS;
        }

        $gameUnits = $gameUnitRegistry->findByCategory($gameUnitCategory);

        return $this->render(
            'site/guide/listGameUnits.html.twig',
            [
                'gameUnitCategory' => $gameUnitCategory,
                'gameUnitCategories' => GameUnitCategory::getAll(),
                'gameUnits' => $gameUnits
            ]
        );
    }

    public function logOff(): Response
    {
        return $this->render('site/guide/logOff.html.twig');
    }

    public function market(): Response
    {
        return $this->render('site/guide/market.html.twig');
    }

    public function region(): Response
    {
        return $this->render('site/guide/region.html.twig');
    }

    public function research(): Response
    {
        return $this->render('site/guide/research.html.twig');
    }

    public function rules(): Response
    {
        return $this->render('site/guide/rules.html.twig');
    }

    public function world(): Response
    {
        return $this->render('site/guide/world.html.twig');
    }
}

<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Exception;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor\ConstructionProcessor;
use FrankProjects\UltimateWarfare\Service\GameEngine\Processor\ResearchProcessor;

final class GameEngine
{
    private ConstructionProcessor $constructionProcessor;
    private ResearchProcessor $researchProcessor;
    private PlayerRepository $playerRepository;

    public function __construct(
        ConstructionProcessor $constructionProcessor,
        ResearchProcessor $researchProcessor,
        PlayerRepository $playerRepository
    ) {
        $this->constructionProcessor = $constructionProcessor;
        $this->researchProcessor = $researchProcessor;
        $this->playerRepository = $playerRepository;
    }

    public function run(?Player $player): void
    {
        $timestamp = time();

        if ($player !== null) {
            $this->processPlayerIncome($player, $timestamp);
        }

        $this->constructionProcessor->run($timestamp);
        $this->researchProcessor->run($timestamp);
        $this->processPopulationGrowth($timestamp);
    }

    public function processPlayerIncome(Player $player, int $timestamp): bool
    {
        // Don't update player income more than once every minute...
        if ($player->getTimestampUpdate() + 60 > $timestamp) {
            return true;
        }

        $timeDiff = $timestamp - $player->getTimestampUpdate();
        $income = $player->getIncome();
        $upkeep = $player->getUpkeep();
        $resources = $player->getResources();
        $incomeCashRate = $income->getCash() - $upkeep->getCash();
        $incomeFoodRate = $income->getFood() - $upkeep->getFood();
        $incomeWoodRate = $income->getWood() - $upkeep->getWood();
        $incomeSteelRate = $income->getSteel() - $upkeep->getSteel();

        $incomeCash = intval(($incomeCashRate / 3600) * $timeDiff);
        $incomeFood = intval(($incomeFoodRate / 3600) * $timeDiff);
        $incomeWood = intval(($incomeWoodRate / 3600) * $timeDiff);
        $incomeSteel = intval(($incomeSteelRate / 3600) * $timeDiff);

        $newCash = $resources->getCash() + $incomeCash;
        if ($newCash < 0) {
            $newCash = 0;
        }

        $newFood = $resources->getFood() + $incomeFood;
        if ($newFood < 0) {
            $newFood = 0;
        }

        $newWood = $resources->getWood() + $incomeWood;
        if ($newWood < 0) {
            $newWood = 0;
        }

        $newSteel = $resources->getSteel() + $incomeSteel;
        if ($newSteel < 0) {
            $newSteel = 0;
        }

        $resources->setCash($newCash);
        $resources->setFood($newFood);
        $resources->setWood($newWood);
        $resources->setSteel($newSteel);

        $player->setResources($resources);
        $player->setTimestampUpdate($timestamp);

        try {
            $this->playerRepository->save($player);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function processPopulationGrowth(int $timestamp): void
    {
        /**
         * //income
         * $constant_income_pop = 0.1;
         *
         * //units
         * $constant_house_pop = 500;
         * $constant_house_pop_growth = 10;
         *
         * $income_pop_increase = 0;
         *
         * $query = "SELECT id, pop, (SELECT ifnull(amount,0) FROM world_region_units WHERE region_id = world_region.id AND unit_id = 5) as house FROM world_region WHERE owner = $player_id";
         * $result = $db->query($query);
         * if ($db->num_rows($result) > 0) {
         * while ($arr = $db->fetch_assoc($result)) {
         * //Set to 0 if NULL is found
         * $arr['house'] = $arr['house'] == "" ? "0" : "".$arr['house']."";
         *
         * if(round($arr['pop']) < ($arr['house'] * $constant_house_pop)){
         * //update pop
         * $pop_growth = (($constant_house_pop_growth / 3600) * $arr['house']) * $time_diff;
         * if(($pop_growth + $arr['pop']) > ($arr['house'] * $constant_house_pop)){
         * $pop_growth = ($arr['house'] * $constant_house_pop) - $arr['pop'];
         * }
         * $pop_growth = round($pop_growth, 3);
         * //update region pop
         * $db->query("UPDATE world_region SET pop = pop + $pop_growth WHERE id = ".$arr['id']."");
         * $income_pop_increase = round($income_pop_increase + ($pop_growth * $constant_income_pop), 3);
         * }
         * }
         * }
         * //update player income
         * $db->query("UPDATE player SET income_cash = income_cash + $income_pop_increase WHERE id = $player_id");
         */
    }
}

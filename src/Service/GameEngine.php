<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;

final class GameEngine
{
    /**
     * @var ConstructionRepository
     */
    private $constructionRepository;

    /**
     * @var EntityManager $em
     */
    private $em;

    /**
     * GameEngine constructor.
     *
     * @param ConstructionRepository $constructionRepository
     * @param EntityManager $em
     */
    public function __construct(ConstructionRepository $constructionRepository, EntityManager $em)
    {
        $this->constructionRepository = $constructionRepository;
        $this->em = $em;
    }

    /**
     * @param Player|null $player
     * @throws \Exception
     */
    public function run(?Player $player)
    {
        $timestamp = time();

        $this->em->getConnection()->beginTransaction();
        try {
            if ($player !== null) {
                $this->processPlayerIncome($player, $timestamp);
            }

            $this->processConstructionQueue($timestamp);
            $this->processResearchQueue($timestamp);
            $this->processPopulationGrowth($timestamp);

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function processConstructionQueue(int $timestamp): bool
    {
        $constructions = $this->constructionRepository->getCompletedConstructions($timestamp);

        /** @var \FrankProjects\UltimateWarfare\Entity\Construction $construction */
        foreach ($constructions as $construction) {
            $worldRegion = $construction->getWorldRegion();

            if ($worldRegion->getPlayer()->getId() !== $construction->getPlayer()->getId()) {
                // Never process construction queue items for a region that no longer belongs to this player
                try {
                    $this->constructionRepository->remove($construction);
                } catch (ORMException $e) {
                    return false;
                }
                continue;
            }

            // Process income before updating income...
            $this->processPlayerIncome($construction->getPlayer(), $timestamp);

            $gameUnitExist = false;
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getId() == $construction->getGameUnit()->getId()) {
                    $gameUnitExist = true;
                    break;
                }
            }

            if ($gameUnitExist) {
                $worldRegionUnit->setAmount($construction->getNumber());
            } else {
                $worldRegionUnit = WorldRegionUnit::create($worldRegion, $construction->getGameUnit(), $construction->getNumber());
            }

            $networth = $construction->getNumber() * $construction->getGameUnit()->getNetworth();

            $upkeepCash = $construction->getNumber() * $construction->getGameUnit()->getUpkeepCash();
            $upkeepFood = $construction->getNumber() * $construction->getGameUnit()->getUpkeepFood();
            $upkeepWood = $construction->getNumber() * $construction->getGameUnit()->getUpkeepWood();
            $upkeepSteel = $construction->getNumber() * $construction->getGameUnit()->getUpkeepSteel();

            $incomeCash = $construction->getNumber() * $construction->getGameUnit()->getIncomeCash();
            $incomeFood = $construction->getNumber() * $construction->getGameUnit()->getIncomeFood();
            $incomeWood = $construction->getNumber() * $construction->getGameUnit()->getIncomeWood();
            $incomeSteel = $construction->getNumber() * $construction->getGameUnit()->getIncomeSteel();

            $player = $construction->getPlayer();
            $player->setNetworth($player->getNetworth() + $networth);

            $player->setUpkeepCash($player->getUpkeepCash() + $upkeepCash);
            $player->setUpkeepFood($player->getUpkeepFood() + $upkeepFood);
            $player->setUpkeepWood($player->getUpkeepWood() + $upkeepWood);
            $player->setUpkeepSteel($player->getUpkeepSteel() + $upkeepSteel);

            $player->setIncomeCash($player->getIncomeCash() + $incomeCash);
            $player->setIncomeFood($player->getIncomeFood() + $incomeFood);
            $player->setIncomeWood($player->getIncomeWood() + $incomeWood);
            $player->setIncomeSteel($player->getIncomeSteel() + $incomeSteel);

            $federation = $player->getFederation();

            $reportType = 2;
            if ($construction->getNumber() > 1) {
                $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getNameMulti()}!";
            } else {
                $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getName()}!";
            }

            $finishedConstructionTime = $construction->getTimestamp() + $construction->getGameUnit()->getTimestamp();
            $report = Report::create($player, $finishedConstructionTime, $reportType, $message);

            try {
                if ($federation !== null) {
                    $federation->setNetworth($federation->getNetworth() + $networth);
                    $this->em->persist($federation);
                }

                $this->em->persist($worldRegionUnit);
                $this->em->persist($player);
                $this->em->persist($report);
                $this->constructionRepository->remove($construction);
                $this->em->flush();
            } catch (ORMException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $timestamp
     * @return bool
     * @throws \Exception
     */
    public function processResearchQueue(int $timestamp): bool
    {
        $researches = $this->em->getRepository('Game:ResearchPlayer')
            ->getNonActiveCompletedResearch($timestamp);

        /** @var \FrankProjects\UltimateWarfare\Entity\ResearchPlayer $researchPlayer */
        foreach ($researches as $researchPlayer) {
            // Process income before updating income...
            $this->processPlayerIncome($researchPlayer->getPlayer(), $timestamp);

            $researchPlayer->setActive(true);

            $researchNetworth = 1250;
            $player = $researchPlayer->getPlayer();
            $player->setNetworth($player->getNetworth() + $researchNetworth);

            $federation = $player->getFederation();

            $research = $researchPlayer->getResearch();
            $finishedTimestamp = $researchPlayer->getTimestamp() + $research->getTimestamp();
            $message = "You succesfully researched a new technology: {$research->getName()}";
            $report = Report::create($player, $finishedTimestamp, 2, $message);

            try {
                if ($federation !== null) {
                    $federation->setNetworth($federation->getNetworth() + $researchNetworth);
                    $this->em->persist($federation);
                }

                $this->em->persist($report);
                $this->em->persist($researchPlayer);
                $this->em->persist($player);
                $this->em->flush();
            } catch (ORMException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Player $player
     * @param int $timestamp
     * @return bool
     */
    public function processPlayerIncome(Player $player, int $timestamp): bool
    {
        // Don't update player income more than once every minute...
        if ($player->getTimestampUpdate() + 60 > $timestamp) {
            return true;
        }

        $timeDiff = $timestamp - $player->getTimestampUpdate();

        $incomeCashRate = $player->getIncomeCash() - $player->getUpkeepCash();
        $incomeFoodRate = $player->getIncomeFood() - $player->getUpkeepFood();
        $incomeWoodRate = $player->getIncomeWood() - $player->getUpkeepWood();
        $incomeSteelRate = $player->getIncomeSteel() - $player->getUpkeepSteel();
        
        $incomeCash = round(($incomeCashRate / 3600) * $timeDiff, 3);
        $incomeFood = round(($incomeFoodRate / 3600) * $timeDiff, 3);
        $incomeWood = round(($incomeWoodRate / 3600) * $timeDiff, 3);
        $incomeSteel = round(($incomeSteelRate / 3600) * $timeDiff, 3);

        $newCash = $player->getCash() + $incomeCash;
        if ($newCash < 0) {
            $newCash = 0;
        }

        $newFood = $player->getFood() + $incomeFood;
        if ($newFood < 0) {
            $newFood = 0;
        }

        $newWood = $player->getWood() + $incomeWood;
        if ($newWood < 0) {
            $newWood = 0;
        }

        $newSteel = $player->getSteel() + $incomeSteel;
        if ($newSteel < 0) {
            $newSteel = 0;
        }

        $player->setCash($newCash);
        $player->setFood($newFood);
        $player->setWood($newWood);
        $player->setSteel($newSteel);
        $player->setTimestampUpdate($timestamp);

        try {
            $this->em->persist($player);
            $this->em->flush();
        } catch (ORMException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param int $timestamp
     */
    public function processPopulationGrowth(int $timestamp)
    {
        /**
         * //income
        $constant_income_pop = 0.1;

        //units
        $constant_house_pop = 500;
        $constant_house_pop_growth = 10;

        $income_pop_increase = 0;

        $query = "SELECT id, pop, (SELECT ifnull(amount,0) FROM world_region_units WHERE region_id = world_region.id AND unit_id = 5) as house FROM world_region WHERE owner = $player_id";
        $result = $db->query($query);
        if ($db->num_rows($result) > 0) {
            while ($arr = $db->fetch_assoc($result)) {
                //Set to 0 if NULL is found
                $arr['house'] = $arr['house'] == "" ? "0" : "".$arr['house']."";

                if(round($arr['pop']) < ($arr['house'] * $constant_house_pop)){
                    //update pop
                    $pop_growth = (($constant_house_pop_growth / 3600) * $arr['house']) * $time_diff;
                    if(($pop_growth + $arr['pop']) > ($arr['house'] * $constant_house_pop)){
                        $pop_growth = ($arr['house'] * $constant_house_pop) - $arr['pop'];
                    }
                    $pop_growth = round($pop_growth, 3);
                    //update region pop
                    $db->query("UPDATE world_region SET pop = pop + $pop_growth WHERE id = ".$arr['id']."");
                    $income_pop_increase = round($income_pop_increase + ($pop_growth * $constant_income_pop), 3);
                }
            }
        }
        //update player income
        $db->query("UPDATE player SET income_cash = income_cash + $income_pop_increase WHERE id = $player_id");

 */
    }
}

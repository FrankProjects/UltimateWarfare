<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Report;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;

final class GameEngine
{
    /**
     * @var ConstructionRepository
     */
    private $constructionRepository;

    /**
     * @var ResearchPlayerRepository
     */
    private $researchPlayerRepository;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * GameEngine constructor.
     *
     * @param ConstructionRepository $constructionRepository
     * @param ResearchPlayerRepository $researchPlayerRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(
        ConstructionRepository $constructionRepository,
        ResearchPlayerRepository $researchPlayerRepository,
        EntityManagerInterface $em
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->researchPlayerRepository = $researchPlayerRepository;
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
     * @param int $timestamp
     * @return bool
     * @throws \Exception
     */
    public function processConstructionQueue(int $timestamp): bool
    {
        $constructions = $this->constructionRepository->getCompletedConstructions($timestamp);

        foreach ($constructions as $construction) {
            $worldRegion = $construction->getWorldRegion();

            if ($worldRegion->getPlayer()->getId() !== $construction->getPlayer()->getId()) {
                // Never process construction queue items for a region that no longer belongs to this player
                try {
                    $this->constructionRepository->remove($construction);
                } catch (\Exception $e) {
                    return false;
                }
                continue;
            }

            // Process income before updating income...
            $this->processPlayerIncome($construction->getPlayer(), $timestamp);

            $gameUnitExist = false;
            foreach ($worldRegion->getWorldRegionUnits() as $worldRegionUnit) {
                if ($worldRegionUnit->getGameUnit()->getId() == $construction->getGameUnit()->getId()) {
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

            $resources = $player->getResources();
            $resources->setUpkeepCash($resources->getUpkeepCash() + $upkeepCash);
            $resources->setUpkeepFood($resources->getUpkeepFood() + $upkeepFood);
            $resources->setUpkeepWood($resources->getUpkeepWood() + $upkeepWood);
            $resources->setUpkeepSteel($resources->getUpkeepSteel() + $upkeepSteel);

            $resources->setIncomeCash($resources->getIncomeCash() + $incomeCash);
            $resources->setIncomeFood($resources->getIncomeFood() + $incomeFood);
            $resources->setIncomeWood($resources->getIncomeWood() + $incomeWood);
            $resources->setIncomeSteel($resources->getIncomeSteel() + $incomeSteel);

            $player->setResources($resources);
            $federation = $player->getFederation();

            $reportType = 2;
            if ($construction->getNumber() > 1) {
                $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getNameMulti()}!";
            } else {
                $message = "You completed {$construction->getNumber()} {$construction->getGameUnit()->getName()}!";
            }

            $finishedConstructionTime = $construction->getTimestamp() + $construction->getGameUnit()->getTimestamp();
            $report = Report::createForPlayer($player, $finishedConstructionTime, $reportType, $message);

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
            } catch (\Exception $e) {
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
        $researches = $this->researchPlayerRepository->getNonActiveCompletedResearch($timestamp);

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
            $message = "You successfully researched a new technology: {$research->getName()}";
            $report = Report::createForPlayer($player, $finishedTimestamp, 2, $message);

            try {
                if ($federation !== null) {
                    $federation->setNetworth($federation->getNetworth() + $researchNetworth);
                    $this->em->persist($federation);
                }

                $this->em->persist($report);
                $this->researchPlayerRepository->save($researchPlayer);
                $this->em->persist($player);
                $this->em->flush();
            } catch (\Exception $e) {
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
        $resources = $player->getResources();
        $incomeCashRate = $resources->getIncomeCash() - $resources->getUpkeepCash();
        $incomeFoodRate = $resources->getIncomeFood() - $resources->getUpkeepFood();
        $incomeWoodRate = $resources->getIncomeWood() - $resources->getUpkeepWood();
        $incomeSteelRate = $resources->getIncomeSteel() - $resources->getUpkeepSteel();
        
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
            $this->em->persist($player);
            $this->em->flush();
        } catch (\Exception $e) {
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

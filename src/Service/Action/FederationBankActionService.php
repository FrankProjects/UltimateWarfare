<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use RuntimeException;

final class FederationBankActionService
{
    /**
     * @var FederationRepository
     */
    private $federationRepository;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * FederationBankActionService constructor.
     *
     * @param FederationRepository $federationRepository
     * @param PlayerRepository $playerRepository
     */
    public function __construct(
        FederationRepository $federationRepository,
        PlayerRepository $playerRepository
    ) {
        $this->federationRepository = $federationRepository;
        $this->playerRepository = $playerRepository;
    }

    /**
     * @param Player $player
     * @param array $resources
     */
    public function deposit(Player $player, array $resources): void
    {
        $this->ensureFederationEnabled($player);

        foreach ($resources as $resourceName => $amount) {
            $this->ensureValidResourcename($resourceName);

            if ($amount < 0) {
                throw new RunTimeException("You can't deposit negative {$resourceName}!");
            }

            if ($amount > $player->getResources()->$resourceName) {
                throw new RunTimeException("You don't have enough {$resourceName}!");
            }
        }

        /**

        $db->query("UPDATE player set cash = cash - $b_cash, wood = wood - $b_wood, steel = steel - $b_steel, food = food - $b_food WHERE id = $player_id");

        $db->query("UPDATE federation set cashbank = cashbank + $b_cash, woodbank = woodbank + $b_wood, steelbank = steelbank + $b_steel, foodbank = foodbank + $b_food WHERE id = $fed_id");


        $updatenews = "INSERT INTO fed_news (world_id, fed_id, timestamp, news) VALUES ($world_id,$fed_id,$time,'".$player['name']." Donated $b_cash cash, $b_wood wood, $b_steel steel and $b_food food to the Federation Bank')";
        $result = $db->query($updatenews);

        $success[] = '* You succesfully made a Donation...';

        $player['cash'] = $player['cash'] - $b_cash;
        $player['wood'] = $player['wood'] - $b_wood;
        $player['steel'] = $player['steel'] - $b_steel;
        $player['food'] = $player['food'] - $b_food;

        $cashbank = $cashbank + $b_cash;
        $woodbank = $woodbank + $b_wood;
        $steelbank = $steelbank + $b_steel;
        $foodbank = $foodbank + $b_food;
        }
        }
        }
         */
    }

    /**
     * @param Player $player
     * @param array $resources
     */
    public function withdraw(Player $player, array $resources): void
    {
        $this->ensureFederationEnabled($player);

        if ($player->getFederationHierarchy() < Player::FEDERATION_HIERARCHY_CAPTAIN) {
            throw new RunTimeException("You don't have permission to use the Federation Bank!");
        }

        foreach ($resources as $resourceName => $amount) {
            $this->ensureValidResourcename($resourceName);

            if ($amount < 0) {
                throw new RunTimeException("You can't withdraw negative {$resourceName}!");
            }

            if ($amount > $player->getFederation()->getResources()->$resourceName) {
                throw new RunTimeException("Federation Bank doesn't have enough {$resourceName}!");
            }
        }
        /**

        $db->query("UPDATE player set cash = cash + $b_cash, wood = wood + $b_wood, steel = steel + $b_steel, food = food + $b_food WHERE id = $player_id");

        $db->query("UPDATE federation set cashbank = cashbank - $b_cash, woodbank = woodbank - $b_wood, steelbank = steelbank - $b_steel, foodbank = foodbank - $b_food WHERE id = $fed_id");

        $updatenews = "INSERT INTO fed_news (world_id, fed_id, timestamp, news) VALUES ($world_id,$fed_id,$time,'".$player['name']." Withdrew $b_cash cash, $b_wood wood, $b_steel steel and $b_food food from the Federation Bank')";
        $result = $db->query($updatenews);

        $success[] = '* You succesfully made a Withdrawal...';

         */
    }

    /**
     * @param Player $player
     */
    private function ensureFederationEnabled(Player $player): void
    {
        $world = $player->getWorld();
        if (!$world->getFederation()) {
            throw new RunTimeException("Federations not enabled!");
        }
    }

    /**
     * XXX TODO: Fix me
     *
     * @param string $resourceName
     */
    private function ensureValidResourceName(string $resourceName): void
    {
        $validResourceNames = ['cash', 'wood', 'steel', 'food'];
        if (!in_array($resourceName, $validResourceNames)) {
            throw new RunTimeException("Invalid resource type {$resourceName}!");
        }
    }
}

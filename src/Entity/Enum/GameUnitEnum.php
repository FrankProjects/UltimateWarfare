<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Enum;

enum GameUnitEnum: int
{
    // Buildings (Category 1)
    case ECONOMIC_CENTER = 1;
    case FARM = 2;
    case IRON_MINE = 3;
    case WOODCUTTER = 4;
    case HOUSE = 5;

    // Defense Buildings (Category 2)
    case SEA_MINE = 100;
    case MINE_FIELD = 101;
    case BUNKER = 102;
    case ANTI_AIRCRAFT_GUN = 103;

    // Special Buildings (Category 3)
    case AIRFIELD = 200;
    case HARBOR = 201;
    case TRAIN_STATION = 202;
    case BARRACK = 203;
    case FACTORY = 204;
    case RADAR_STATION = 205;
    case MISSILE_FACTORY = 206;

    // Troops (Category 6)
    case SOLDIER = 600;
    case SNIPER = 601;
    case TANK = 602;
    case ARTILLERY = 603;
    case MINE_SWEEPER = 604;

    // Naval Units (Category 7)
    case PATROL_BOAT = 700;
    case DESTROYER = 701;
    case CRUISER = 702;
    case SUBMARINE = 703;
    case MINE_COUNTERMEASURES_SHIP = 704;

    // Air Units (Category 8)
    case FIGHTER = 800;
    case BOMBER = 801;
    case STRATEGIC_BOMBER = 802;

    // Missiles (Category 9)
    case ROCKET = 900;
    case CHEMICAL_ROCKET = 901;
    case NUCLEAR_MISSILE = 902;
}

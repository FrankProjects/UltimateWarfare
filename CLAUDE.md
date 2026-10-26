# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Ultimate Warfare is a browser-based multiplayer strategy game built with **Symfony 8.0** and **PHP 8.4**. Players compete through resource management, construction, research, fleet operations, and battles.

## Common Commands

```bash
# Dependencies
composer install

# Cache
bin/console cache:clear

# Queue worker: keeps completing due construction/research (production: systemd unit
# in config/systemd/ultimatewarfare-worker.service); --once for a single pass
bin/console game:queue:work
bin/console game:queue:work --once

# Tests
php bin/phpunit
php bin/phpunit tests/Util/DistanceCalculatorTest.php   # Single test file

# Code quality (run before committing)
vendor/bin/phpcs                          # PSR-12 style check
vendor/bin/phpstan analyse               # Static analysis (level 10)
```

## Architecture

### Layers

- **Controllers** (`src/Controller/`) — HTTP handlers split into `Admin/`, `Game/`, `Forum/`, `Site/`
- **Services** (`src/Service/`) — All business logic lives here
- **Entities** (`src/Entity/`) — Doctrine ORM entities; mappings are in XML (`config/doctrine/`)
- **Repositories** (`src/Repository/`) — Data access with custom query methods
- **Commands** (`src/Command/`) — CLI commands for the queue worker and maintenance

### Key Services

- **PlayerStateUpdaterService** — Brings a player's stored state up to a moment (`updateStateForPlayer()`): completes queued construction and research at their completion time, grows the population (or starves the player) at every population tick and accrues income/upkeep per interval; run by the queue worker for all players with due items, growing population or starvation, and at the start of every state-changing request for the current player
- **BattleEngine** — Multi-phase combat simulation, casualty calculation, battle report generation
- **FleetActionService / RegionActionService** — High-level fleet operations (send, recall, attack, pillage)
- **OperationService** — Covert ops (spying, sabotage, conversion) with success probability
- **WorldGeneratorService** — Procedural world/region creation

### Game Systems

- **Construction queue**: Buildings/research have (indexed) completion timestamps; the `game:queue:work` worker resolves them within seconds. GET requests never update player state; the client reloads data a few seconds after a completion (`ResourceTicker.completionDelaySeconds`)
- **Population**: each House holds 500 people in its region; population grows by 1 per House every 6-minute tick (`PopulationUpdaterService`, ticks on the Unix epoch) and every 15 people add 1 cash/hour to income. Troops, aircraft and ships are crewed by people: training one takes `GameUnit::getPopulation()` people from the region it is trained in when it is queued (not returned on cancel), so army growth is limited by Houses. On the same tick a player at 0 food with a food deficit starves instead (`StarvationUpdaterService`): regions lose 3.5% population per tick (30% per hour); units are not affected. Ticking keeps the income rate constant between ticks, so accrual stays exact. Regions start at 0 population and are reset to 0 when they lose their owner
- **Fleet travel**: Fleets are entities in transit. `FleetRouteService` finds the route (BFS over `HexGrid`): ground troops march through the player's own connected land regions and only attack a region next to them, ships sail through own water and open sea (a land region is only entered or left over water), aircraft fly in a straight line. The route is stored as `Fleet::path`; travel time is 200 seconds per step at base speed
- **Water regions**: can't be bought. Naval-only fleets sail to open sea next to an owned region and claim it on arrival ("Occupy" = `FleetActionService::reinforce()`); a recall claims a released origin again. `WaterRegionUpdaterService` releases a water region once it has no units, Sea Mine or Sea Mine construction left: right away when units leave or a defence is lost, otherwise (e.g. operations) via a sweep in every `game:queue:work` pass. Only Sea Mines can be built on water. Coastal regions are land regions sharing a border with a water region (`WorldRegion::isCoastal()`); Harbors need one, Sea Mines need a coastal or water region, ships attack from and to water or coastal regions
- **Radar**: a Radar Station reveals every region within a radius equal to its level (`RadarService::getCoverage()`) and detects enemy fleets (other players, not federation members) whose route crosses that area; the map draws them as red dotted lines with an ETA, without units or owner
- **World map v2**: Interactive map in `public/js/world/` and `templates/v2/`; active development area
- **Request transaction**: `PlayerRequestSubscriber` runs every state-changing (non-GET) player request in one DB transaction holding a row lock on the current player; it commits for responses < 400 and rolls back otherwise. Game actions must therefore be POST routes and report failures with a 4xx status. An action that also changes another player must call `PlayerStateUpdaterService::updateStateForPlayer()` on them first; shared rows (market orders, fleets, regions, federations) are locked with `EntityLockRepository::lockForUpdate()`
- **Income accrual**: Resources are stored as a checkpoint (`timestampUpdate`) plus the income/upkeep rate; `ResourceAccrualCalculator` accrues exactly. Recalculate the rate with `IncomeUpdaterService` after any change to units, regions or research; read current values for display with `project()` / the `player_resources()` Twig function

### Database

Doctrine ORM with XML mapping files in `config/doctrine/`. All entity relationships are defined there, not via PHP annotations.

### Code Standards

All PHP files use `declare(strict_types=1)`. PHPStan runs at level 10 with Symfony and Doctrine extensions. New code must pass both `phpcs` (PSR-12) and `phpstan` before merging.

### Environment

Key `.env` variables: `DATABASE_URL`, `MAILER_DSN`, `RECAPTCHA_PUBLIC_KEY`/`RECAPTCHA_PRIVATE_KEY`, and game override flags (`GAME_OVERRIDE_CONSTRUCTION_SECONDS=1` for instant builds in development).

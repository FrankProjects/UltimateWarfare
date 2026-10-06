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

# Game engine tick (processes income, construction, research)
bin/console game:engine:run

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
- **Commands** (`src/Command/`) — CLI commands for game engine and maintenance

### Key Services

- **GameEngine** — Core tick processor: calculates income/upkeep per elapsed time, completes queued constructions and research
- **BattleEngine** — Multi-phase combat simulation, casualty calculation, battle report generation
- **FleetActionService / RegionActionService** — High-level fleet operations (send, recall, attack, pillage)
- **OperationService** — Covert ops (spying, sabotage, conversion) with success probability
- **WorldGeneratorService** — Procedural world/region creation

### Game Systems

- **Real-time income**: Resources accumulate continuously based on time deltas, processed on each engine tick
- **Construction queue**: Buildings/research have completion timestamps; engine resolves them on tick
- **Fleet travel**: Distance-based travel time; fleets are entities in transit with action types
- **World map v2**: Interactive map in `public/js/world/` and `templates/v2/`; active development area

### Database

Doctrine ORM with XML mapping files in `config/doctrine/`. All entity relationships are defined there, not via PHP annotations.

### Code Standards

All PHP files use `declare(strict_types=1)`. PHPStan runs at level 10 with Symfony and Doctrine extensions. New code must pass both `phpcs` (PSR-12) and `phpstan` before merging.

### Environment

Key `.env` variables: `DATABASE_URL`, `MAILER_DSN`, `RECAPTCHA_PUBLIC_KEY`/`RECAPTCHA_PRIVATE_KEY`, and game override flags (`GAME_OVERRIDE_CONSTRUCTION_SECONDS=1` for instant builds in development).

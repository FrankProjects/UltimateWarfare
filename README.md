Ultimate Warfare
================

![Ultimate Warfare](https://raw.githubusercontent.com/FrankProjects/UltimateWarfare/master/public/images/banner.jpg)

[![Travis-CI](https://api.travis-ci.org/FrankProjects/UltimateWarfare.svg)](https://travis-ci.org/FrankProjects/UltimateWarfare)
[![StyleCI](https://github.styleci.io/repos/126375561/shield)](https://github.styleci.io/repos/126375561)
[![Maintainability](https://api.codeclimate.com/v1/badges/2aecf6889aa57d760beb/maintainability)](https://codeclimate.com/github/FrankProjects/UltimateWarfare/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/2aecf6889aa57d760beb/test_coverage)](https://codeclimate.com/github/FrankProjects/UltimateWarfare/test_coverage)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

*Note: Ultimate Warfare is still in development. Some features of the game have not been implemented yet.*


## Introduction

Ultimate Warfare is a free and open-source online multiplayer browser based strategy game where you compete with thousands of others for world domination.

Grow powerful and nuke your enemies! Send sniper teams or special forces demolitions teams to wreak havoc! Watch your enemy with spy satellites and send a squadron of stealth bombers to knock out his key infrastructure! AND MUCH MORE!

Ultimate Warfare is written in PHP on the [Symfony 4](https://symfony.com) framework.

## History

I'm Frank Schreuder, a Software Engineer, Web and Game Developer and enjoy working on my own projects.

I started programming at around 2005. Ultimate Warfare was one of my first projects. I started working on Ultimate Warfare after years of playing [Imperial Conflict](https://imperialconflict.com). The joy of playing a game declined over time and the joy of finding out how a game works and how it's developed, started to gain my interest.

After years of working on Ultimate Warfare I think it's time to share it with everybody. I don't have much time to work on it and I care about learning and sharing.

Over the years I saw a decrease in browser based strategy games. With open-sourcing this game code I hope to inspire other people to build awesome games like this.

Most strategy games are 'tick' based, as so was the first version of Ultimate Warfare. Later the game engine of the game was rewritten to make it real time. I used this game as learning project, as I was looking for more technical challenges I decided to make it real time. This adds more complexity to the code to ensure a consistent game state.

The old game code dates back to 2005-2012 and contains a lot of legacy PHP, for example:
- Not object oriented
- Html in PHP
- PHP 5

In 2017 I started porting the game code to Symfony 4 and twig templates. To learn the symfony framework but also with the idea in mind to publish to game code later.
While porting the game code to symfony, I used more modern development techniques.
The new code base is PHP 7.1 compatible with PHP strict typ hinting.


## Missing features

Missing features compared to old game code:
- Avatar system
- Federations
    - Chat
    - Forum
- Special operations


## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.


## Links

- [Old game](https://ultimate-warfare.com)
- [Issue tracker](https://github.com/FrankProjects/UltimateWarfare/issues)


## License

Ultimate Warfare is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).

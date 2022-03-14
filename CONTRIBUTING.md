# Contributing to Ultimate Warfare

Thank you for considering contributing to Ultimate Warfare. This document contains some guidelines to explain the contributing process and hopefully answer some common questions.

Do note that almost nothing is set in stone. Feel free to even contribute to this document!


#### Table of Contents

- [Before getting started](#before-getting-started)
  - [Prerequisites](#prerequisites)
  - [Vision](#vision)
- [How can I contribute?](#how-can-i-contribute)
  - [Participating in the beta](#participating-in-the-beta)
  - [Reporting bugs](#reporting-bugs)
  - [Collaborating with development](#collaborating-with-development)
- [Local development](#local-development)
  - [Setting up](#setting-up)
  - [Directory structure](#directory-structure)
  - [How to run tests](#how-to-run-tests)
  - [How to update](#how-to-update)
  - [Style guide and standards](#style-guide-and-standards)


## Before getting started

### Prerequisites

- For code contributions, make sure you have a [GitHub account](https://github.com/signup/free)
- Make sure you read, understand and agree to the [Code of Conduct](CODE_OF_CONDUCT.md)

Collaboration and contributing will be primarily done through GitHub.


### Vision

War game, economy, world map, strategy, free and fun!
 
## How can I contribute?

### Participating in the beta

The source code of Ultimate Warfare is playable on [this beta server](https://beta.ultimate-warfare.com). Enjoy the game and please report all issues/suggestions in the [issue tracker](https://github.com/FrankProjects/UltimateWarfare/issues). 

### Reporting bugs

You can report bugs to the [issue tracker](https://github.com/FrankProjects/UltimateWarfare/issues).

Please search the issue tracker first if the particular bug already has an open issue. If it does, add your message to the existing issue instead of opening a new issue.

If a closed or resolved issue exists for your particular bug, reopen it. If in doubt, just open a new issue.


### Collaborating with development

First, make sure the changes you're going to do adhere to the [vision of UltimateWarfare](#vision).

Fork the repository on GitHub, make a new branch off develop and start from there. Separate features isolated from each other should go in their own branch. Branch names should preferably adhere to the Git Flow workflow using a `feature/FeatureName` or `hotfix/HotfixName` notation. 

When making changes, add or modify relevant tests with your changes if it involves game mechanic-related code.

Once you're satisfied with your modifications, send me a pull request. I will review it, edit it as needed and merge it with the develop branch.


## Local development
 
### Setting up

##### Assumptions:

- You have [PHP](http://www.php.net/) 8.0 or higher installed and in your path.
- You have [Composer](https://getcomposer.org/) installed and in your path.
- You have a basic understanding of the [Symfony framework](https://symfony.com/).


##### Languages, frameworks, libraries and tools

Ultimate Warfare is built on the Symfony framework, using modern PHP as language and twig as templating language.

I'm developing Ultimate Warfare in PhpStorm myself, but you're of course free to use whatever you see fit.


##### Cloning the repository:

```bash
$ git pull https://github.com/FrankProjects/UltimateWarfare.git UltimateWarfare
$ cd UltimateWarfare
```


##### Setup

```bash
# Composer stuff
$ composer self-update
$ composer install --prefer-source

```


##### Create database and schema

```bash
$ bin/console doctrine:database:create
$ bin/console doctrine:schema:create
```


##### Load database data

```bash
$ bin/console doctrine:migrations:migrate
```

#### Chat Server

Add to your apache vhost file:
```
ProxyPass /wss/ ws://localhost:8080/
ProxyPassReverse /wss/ ws://localhost:8080/
```

Enable mod_proxy and mod_proxy_wstunnel to handle secure websocket traffic
```bash
$ sudo a2enmod proxy
$ sudo a2enmod proxy_wstunnel
$ sudo systemctl restart apache2
````

Start the ChatServer
```bash
php bin/console chat:start
```

##### Run chat server as a service on Debian
Copy uw-chat.service into /etc/systemd/system/uw-chat.service and enable/start the service
```bash
systemctl enable uw-chat
systemctl start uw-chat
systemctl status uw-chat
```

### Directory structure

```
UltimateWarfare/
├─ assets/
├─ bin/
│  └─ console
├─ config/
├─ public/
│  └─ index.php
├─ src/
│  ├─ Kernel.php
│  ├─ Command/
│  ├─ Controller/
│  ├─ DataFixtures/
│  ├─ Entity/
│  ├─ EventSubscriber/
│  ├─ Form/
│  ├─ Repository/
│  ├─ Security/
│  └─ Twig/
├─ templates/
├─ tests/
├─ translations/
├─ var/
│  ├─ cache/
│  ├─ log/
│  └─ ...
├─ vendor/
└─ .env
```

### How to run tests

You can run tests with:

```bash
$ vendor/bin/phpunit
```

There are two test suites, named as follows:

- Feature Tests
- Unit Tests

Feature tests can be seen as user stories if you're familiar with Agile.

Unit test classes are tests that generally correspond to a single source class to test the implementation of the business logic. Unit tests methods **should** correspond to a matching source class method under test using a `testNameOfMethodUnderTest` naming convention.

Consult [PHPUnit's manual](https://phpunit.de/manual/5.7/en/index.html) for running specific test suites or individual files.


### How to update

For updating your local development environment, do a `git pull`, optionally followed by a `composer install`, depending on which files have changed.


### Style guide and standards

PHP code should be in PSR12-style.

Please add relevant unit tests or feature tests if possible.

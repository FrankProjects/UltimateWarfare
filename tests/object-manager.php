<?php

use FrankProjects\UltimateWarfare\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__ . '/../.env');


/** @var string $appEnv */
$appEnv = $_SERVER['APP_ENV'];
$kernel = new Kernel($appEnv, (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
return $kernel->getContainer()->get('doctrine')->getManager();

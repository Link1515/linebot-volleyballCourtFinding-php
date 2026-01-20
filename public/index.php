<?php

require_once __DIR__ . '/../vendor/autoload.php';

use TerryLin\LineBot\Dependency;
use TerryLin\LineBot\Route;

Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

$container = new \DI\Container();
(new Dependency())->register($container);

$app = \Slim\Factory\AppFactory::createFromContainer($container);
(new Route())->register($app);

$app->run();

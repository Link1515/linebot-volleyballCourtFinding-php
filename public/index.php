<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Lynk\LineBot\Dependency;
use Lynk\LineBot\Route;

Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

$container = new \DI\Container();
(new Dependency())->register($container);

$app = \Slim\Factory\AppFactory::createFromContainer($container);
(new Route())->register($app);

$app->run();

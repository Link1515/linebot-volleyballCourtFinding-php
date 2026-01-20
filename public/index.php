<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

Dotenv::createImmutable(__DIR__ . '/../')->load();

$containerBuilder = new ContainerBuilder();

$isProduction = $_ENV['APP_ENV'] === 'production';
if ($isProduction) {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache/php-di');
}

$containerBuilder->addDefinitions(__DIR__ . '/../config/definitions.php');

$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

(require __DIR__ . '/../config/middlewares.php')($app);
(require __DIR__ . '/../config/routes.php')($app);

$app->run();

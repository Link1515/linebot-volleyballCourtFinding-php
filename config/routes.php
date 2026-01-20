<?php

declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use TerryLin\LineBot\Controller\WebhookController;

return function (App $app): void {
    $app->post('/webhook', WebhookController::class);

    $app->get('/', function (RequestInterface $req, ResponseInterface $res) {
        $res->getBody()->write('hello line');

        return $res;
    });
};

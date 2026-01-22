<?php

declare(strict_types=1);

use Slim\App;
use Slim\Exception\HttpNotFoundException;

return function (App $app): void {
    $app->addRoutingMiddleware();

    $errorMiddleware = $app->addErrorMiddleware(
        false,
        false,
        false
    );

    $errorMiddleware->setErrorHandler(
        HttpNotFoundException::class,
        function ($request, $response, $exception) use ($app) {
            $response = $app->getResponseFactory()->createResponse(404);
            $response->getBody()->write('Not Found');
            $response = $response->withHeader('Content-Type', 'text/plain; charset=utf-8');
            return $response;
        }
    );
};

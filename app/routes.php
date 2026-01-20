<?php

declare(strict_types=1);

use Slim\App;
use TerryLin\LineBot\Controller\WebhookController;

return function (App $app): void {
    $app->post('/webhook', WebhookController::class);
};

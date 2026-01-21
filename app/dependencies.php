<?php

declare(strict_types=1);
use GuzzleHttp\Client;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use TerryLin\LineBot\EventHandler;
use TerryLin\LineBot\Settings;

return [
    Settings::class => function () {
        return new Settings([
            'logger' => [
                'name' => 'slim-app',
                'path' => __DIR__ . '/../storage/logs/app.log',
            ],

            'bot' => [
                'channelToken'  => $_ENV['LINEBOT_CHANNEL_TOKEN'],
                'channelSecret' => $_ENV['LINEBOT_CHANNEL_SECRET'],
            ],
        ]);
    },

    LoggerInterface::class => function ($c) {
        $settings = $c->get(Settings::class);
        $logger   = new Logger($settings->get('logger.name'));
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        $logger->pushHandler(new StreamHandler($settings->get('logger.path'), Level::Debug));
        return $logger;
    },

    MessagingApiApi::class => function ($c) {
        $settings     = $c->get(Settings::class);
        $channelToken = $settings->get('bot.channelToken');
        $config       = new Configuration();
        $config->setAccessToken($channelToken);
        $bot = new MessagingApiApi(
            client: new Client(),
            config: $config,
        );
        return $bot;
    },

    EventHandler::class => function ($c) {
        return new EventHandler($c->get(MessagingApiApi::class));
    },
];

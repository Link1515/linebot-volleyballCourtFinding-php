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

return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production

        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
        ],

        'bot' => [
            'channelToken'  => $_ENV['LINEBOT_CHANNEL_TOKEN'],
            'channelSecret' => $_ENV['LINEBOT_CHANNEL_SECRET'],
        ],
    ],

    LoggerInterface::class => function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger   = new Logger($settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        $logger->pushHandler(new StreamHandler($settings['path'], Level::Debug));
        return $logger;
    },

    MessagingApiApi::class => function ($c) {
        $settings     = $c->get('settings');
        $channelToken = $settings['bot']['channelToken'];
        $config       = new Configuration();
        $config->setAccessToken($channelToken);
        $bot = new MessagingApiApi(
            client: new Client(),
            config: $config,
        );
        return $bot;
    },
];

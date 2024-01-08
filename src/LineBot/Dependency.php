<?php

namespace Lynk\LineBot;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;

class Dependency
{
    public function register(\DI\Container $container)
    {
        $container->set('settings', function ($c) {
            return Setting::getSetting()['settings'];
        });

        $container->set(\Psr\Log\LoggerInterface::class, function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Level::Debug));
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Level::Debug));
            return $logger;
        });

        $container->set('botMessagingApi', function ($c) {
            $settings = $c->get('settings');
            $channelToken = $settings['bot']['channelToken'];
            $config = new Configuration();
            $config->setAccessToken($channelToken);
            $bot = new MessagingApiApi(
                client: new \GuzzleHttp\Client(),
                config: $config,
            );
            return $bot;
        });
    }
}

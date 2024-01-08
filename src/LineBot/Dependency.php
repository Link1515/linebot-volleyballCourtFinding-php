<?php

namespace Lynk\LineBot;

use DI\Container;
use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Configuration;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use GuzzleHttp\Client;

class Dependency
{
    public function register(Container $container)
    {
        $container->set('settings', function ($c) {
            return Setting::getSetting()['settings'];
        });

        $container->set(LoggerInterface::class, function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new Logger($settings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
            $logger->pushHandler(new StreamHandler($settings['path'], Level::Debug));
            return $logger;
        });

        $container->set(MessagingApiApi::class, function ($c) {
            $settings = $c->get('settings');
            $channelToken = $settings['bot']['channelToken'];
            $config = new Configuration();
            $config->setAccessToken($channelToken);
            $bot = new MessagingApiApi(
                client: new Client(),
                config: $config,
            );
            return $bot;
        });
    }
}

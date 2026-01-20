<?php

declare(strict_types=1);

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Constants\HTTPHeader;
use LINE\Parser\EventRequestParser;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use LINE\Webhook\Model\FollowEvent;
use LINE\Webhook\Model\JoinEvent;
use LINE\Webhook\Model\LocationMessageContent;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\PostbackEvent;
use LINE\Webhook\Model\TextMessageContent;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use TerryLin\LineBot\EventHandler\EventHandlerInterface;
use TerryLin\LineBot\EventHandler\FollowEventHandler;
use TerryLin\LineBot\EventHandler\JoinEventHandler;
use TerryLin\LineBot\EventHandler\MessageHandler\LocationMessageHandler;
use TerryLin\LineBot\EventHandler\MessageHandler\TextMessageHandler;
use TerryLin\LineBot\EventHandler\PostbackEventHandler;

return function (App $app): void {
    $app->post('/webhook', function (RequestInterface $req, ResponseInterface $res) {
        // send response immediately by fastcgi_finish_request
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        /** @var \LINE\Clients\MessagingApi\Api\MessagingApiApi $bot */
        $bot = $this->get(MessagingApiApi::class);
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->get(\Psr\Log\LoggerInterface::class);

        $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            return $res->withStatus(400, 'Bad Request');
        }

        /**
         * Check request with signature and parse request
         */
        try {
            $secret       = $this->get('settings')['bot']['channelSecret'];
            $body         = $req->getBody()->getContents();
            $parsedEvents = EventRequestParser::parseEventRequest($body, $secret, $signature[0]);
        } catch (InvalidSignatureException $e) {
            return $res->withStatus(400, 'Invalid signature');
        } catch (InvalidEventRequestException $e) {
            return $res->withStatus(400, "Invalid event request");
        }

        /**
         * handle different types of events
         */
        foreach ($parsedEvents->getEvents() as $event) {
            /** @var EventHandlerInterface $handler */
            $handler = null;

            if ($event instanceof MessageEvent) {
                $message = $event->getMessage();
                if ($message instanceof TextMessageContent) {
                    $handler = new TextMessageHandler($bot, $logger, $req, $event);
                } elseif ($message instanceof LocationMessageContent) {
                    $handler = new LocationMessageHandler($bot, $logger, $event);
                }
            } elseif ($event instanceof PostbackEvent) {
                $handler = new PostbackEventHandler($bot, $logger, $event);
            } elseif ($event instanceof FollowEvent) {
                $handler = new FollowEventHandler($bot, $logger, $event);
            } elseif ($event instanceof JoinEvent) {
                $handler = new JoinEventHandler($bot, $logger, $event);
            }

            $handler->handle();
        }

        $res->withStatus(200, 'OK');
        return $res;
    });

    $app->get('/', function (RequestInterface $req, ResponseInterface $res) {
        $res->getBody()->write('hello line');

        return $res;
    });
};

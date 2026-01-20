<?php

namespace Lynk\LineBot;

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
use Lynk\LineBot\EventHandler\FollowEventHandler;
use Lynk\LineBot\EventHandler\JoinEventHandler;
use Lynk\LineBot\EventHandler\PostbackEventHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Lynk\LineBot\EventHandler\EventHandlerInterface;
use Lynk\LineBot\EventHandler\MessageHandler\LocationMessageHandler;
use Lynk\LineBot\EventHandler\MessageHandler\TextMessageHandler;

class Route
{
    public function register(\Slim\App $app)
    {
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
                $secret = $this->get('settings')['bot']['channelSecret'];
                $parsedEvents = EventRequestParser::parseEventRequest($req->getBody(), $secret, $signature[0]);
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
                    } else if ($message instanceof LocationMessageContent) {
                        $handler = new LocationMessageHandler($bot, $logger, $event);
                    }
                } else if ($event instanceof PostbackEvent) {
                    $handler = new PostbackEventHandler($bot, $logger, $event);
                } else if ($event instanceof FollowEvent) {
                    $handler = new FollowEventHandler($bot, $logger, $event);
                } else if ($event instanceof JoinEvent) {
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
    }
}

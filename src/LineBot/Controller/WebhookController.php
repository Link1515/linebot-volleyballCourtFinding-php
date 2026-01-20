<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Controller;

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
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use TerryLin\LineBot\EventHandler\EventHandlerInterface;
use TerryLin\LineBot\EventHandler\FollowEventHandler;
use TerryLin\LineBot\EventHandler\JoinEventHandler;
use TerryLin\LineBot\EventHandler\MessageHandler\LocationMessageHandler;
use TerryLin\LineBot\EventHandler\MessageHandler\TextMessageHandler;
use TerryLin\LineBot\EventHandler\PostbackEventHandler;
use TerryLin\LineBot\Settings;

class WebhookController
{
    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly Settings $settings,
    ) {
    }

    public function __invoke(Request $req, Response $res): Response
    {
        // send response immediately by fastcgi_finish_request
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            return $res->withStatus(400, 'Bad Request');
        }

        /**
         * Check request with signature and parse request
         */
        try {
            $secret       = $this->settings->get('bot.channelSecret');
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
                    $handler = new TextMessageHandler($this->bot, $this->logger, $req, $event);
                } elseif ($message instanceof LocationMessageContent) {
                    $handler = new LocationMessageHandler($this->bot, $this->logger, $event);
                }
            } elseif ($event instanceof PostbackEvent) {
                $handler = new PostbackEventHandler($this->bot, $this->logger, $event);
            } elseif ($event instanceof FollowEvent) {
                $handler = new FollowEventHandler($this->bot, $this->logger, $event);
            } elseif ($event instanceof JoinEvent) {
                $handler = new JoinEventHandler($this->bot, $this->logger, $event);
            }

            $handler->handle();
        }

        $res->withStatus(200, 'OK');
        return $res;
        return $response;
    }
}

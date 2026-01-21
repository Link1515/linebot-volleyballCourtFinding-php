<?php

declare(strict_types=1);

namespace TerryLin\LineBot;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Webhook\Model\Event;
use LINE\Webhook\Model\FollowEvent;
use LINE\Webhook\Model\JoinEvent;
use LINE\Webhook\Model\LocationMessageContent;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\PostbackEvent;
use LINE\Webhook\Model\TextMessageContent;
use TerryLin\LineBot\EventHandler\EventHandlerInterface;
use TerryLin\LineBot\EventHandler\FollowHandler;
use TerryLin\LineBot\EventHandler\JoinHandler;
use TerryLin\LineBot\EventHandler\MessageHandler\LocationHandler;
use TerryLin\LineBot\EventHandler\MessageHandler\TextHandler;
use TerryLin\LineBot\EventHandler\PostbackHandler;

class EventHandler
{
    public function __construct(
        private readonly MessagingApiApi $bot,
    ) {
    }

    public function handle(array $events): void
    {
        /** @var Event $event */
        foreach ($events as $event) {
            /** @var EventHandlerInterface $handler */
            $handler = null;

            if ($event instanceof MessageEvent) {
                $message = $event->getMessage();
                if ($message instanceof TextMessageContent) {
                    $handler = new TextHandler($message);
                } elseif ($message instanceof LocationMessageContent) {
                    $handler = new LocationHandler($message);
                }
            } elseif ($event instanceof PostbackEvent) {
                $handler = new PostbackHandler($event);
            } elseif ($event instanceof FollowEvent) {
                $handler = new FollowHandler();
            } elseif ($event instanceof JoinEvent) {
                $handler = new JoinHandler();
            }

            $this->bot->replyMessage(
                new ReplyMessageRequest([
                    'replyToken' => $event->getReplyToken(),
                    'messages'   => $handler->getReplyMessages(),
                ])
            );
        }
    }
}

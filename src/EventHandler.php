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
use TerryLin\LineBot\Handler\FollowHandler;
use TerryLin\LineBot\Handler\HandlerInterface;
use TerryLin\LineBot\Handler\JoinHandler;
use TerryLin\LineBot\Handler\LocationHandler;
use TerryLin\LineBot\Handler\PostbackHandler;
use TerryLin\LineBot\Handler\TextHandler;

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
            /** @var HandlerInterface $handler */
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

            if (empty($handler)) {
                return;
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

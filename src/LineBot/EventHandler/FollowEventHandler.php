<?php

declare(strict_types=1);

namespace TerryLin\LineBot\EventHandler;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Webhook\Model\FollowEvent;
use LINE\Webhook\Model\PostbackEvent;
use Psr\Log\LoggerInterface;
use TerryLin\LineBot\BotUtils;

class FollowEventHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly FollowEvent $event
    ) {
    }

    public function handle(): void
    {
        $botRequest = BotUtils::createTextReplyRequest(
            $this->event->getReplyToken(),
            '您好，歡迎使用 超級排🏐球場 line機器人'
        );

        $this->bot->replyMessage($botRequest);
    }
}

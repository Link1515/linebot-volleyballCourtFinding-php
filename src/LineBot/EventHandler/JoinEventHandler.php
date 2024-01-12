<?php

declare(strict_types=1);

namespace Lynk\LineBot\EventHandler;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Webhook\Model\FollowEvent;
use LINE\Webhook\Model\JoinEvent;
use LINE\Webhook\Model\PostbackEvent;
use Lynk\LineBot\BotUtils;
use Psr\Log\LoggerInterface;

class JoinEventHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly JoinEvent $event
    ) {
    }

    public function handle(): void
    {
        $botRequest = BotUtils::createTextReplyRequest(
            $this->event->getReplyToken(),
            '大家好，歡迎使用 超級排🏐球場 line機器人'
        );

        $this->bot->replyMessage($botRequest);
    }
}


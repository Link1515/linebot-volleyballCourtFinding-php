<?php

declare(strict_types=1);

namespace TerryLin\LineBot\EventHandler;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;

class FollowHandler implements EventHandlerInterface
{
    public function __construct()
    {
    }

    public function getReplyMessages(): array
    {
        return [
            new TextMessage([
                'type' => MessageType::TEXT,
                'text' => '感謝您的追蹤',
            ])
        ];
    }
}

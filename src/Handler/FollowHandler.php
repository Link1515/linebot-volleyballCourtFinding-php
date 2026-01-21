<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;

class FollowHandler implements HandlerInterface
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

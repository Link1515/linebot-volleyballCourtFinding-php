<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;

class JoinHandler implements HandlerInterface
{
    public function __construct()
    {
    }

    public function getReplyMessages(): array
    {
        return [
            new TextMessage([
                'type' => MessageType::TEXT,
                'text' => '您好，歡迎使用 超級排🏐球場 LINE 機器人',
            ])
        ];
    }
}

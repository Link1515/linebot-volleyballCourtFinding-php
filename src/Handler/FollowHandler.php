<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use TerryLin\LineBot\Helper;

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
                'text' => Helper::t('follow'),
            ])
        ];
    }
}

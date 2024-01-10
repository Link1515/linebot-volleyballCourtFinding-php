<?php

declare(strict_types=1);

namespace Lynk\LineBot;

use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;

class BotUtils
{
    public static function createTextReplyRequest(string $replyToken, string $text): ReplyMessageRequest
    {

        $message = new TextMessage([
            'type' => MessageType::TEXT,
            'text' => $text,
        ]);

        $botRequest = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [$message],
        ]);

        return $botRequest;
    }
}

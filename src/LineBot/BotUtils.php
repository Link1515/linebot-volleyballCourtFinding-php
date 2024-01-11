<?php

declare(strict_types=1);

namespace Lynk\LineBot;

use LINE\Clients\MessagingApi\Model\Message;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;

class BotUtils
{
    public static function createMessageReplyRequest(string $replyToken, Message $message): ReplyMessageRequest
    {
        $botRequest = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [$message],
        ]);

        return $botRequest;
    }

    public static function createTextReplyRequest(string $replyToken, string $text): ReplyMessageRequest
    {
        $textMessage = new TextMessage([
            'type' => MessageType::TEXT,
            'text' => $text,
        ]);

        return self::createMessageReplyRequest($replyToken, $textMessage);
    }
}

<?php

declare(strict_types=1);

namespace Lynk\LineBot;

use LINE\Clients\MessagingApi\Model\Message;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;

class BotUtils
{
    /**
     * @param string $replyToken 
     * @param Message[] $messages
     */
    public static function createMessageReplyRequest(string $replyToken, array $messages): ReplyMessageRequest
    {
        $botRequest = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => $messages,
        ]);

        return $botRequest;
    }

    public static function createTextReplyRequest(string $replyToken, string $text): ReplyMessageRequest
    {
        $textMessage = new TextMessage([
            'type' => MessageType::TEXT,
            'text' => $text,
        ]);

        return self::createMessageReplyRequest($replyToken, [$textMessage]);
    }
}

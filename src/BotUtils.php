<?php

declare(strict_types=1);

namespace TerryLin\LineBot;

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
            'messages'   => $messages,
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

    public static function getCourts(): array
    {
        $courtsFile = __DIR__ . '/../storage/data/courts.json';

        if (!file_exists($courtsFile)) {
            include_once __DIR__ . '/../scripts/fetchCourts.php';
        }

        return json_decode(file_get_contents($courtsFile));
    }

    public static function encodeUrlPath(string $url): string
    {
        $parsedUrl = parse_url($url);

        $pathArray = [];
        if (array_key_exists('path', $parsedUrl)) {
            $pathArray = explode('/', $parsedUrl['path']);
            foreach ($pathArray as &$path) {
                $path = urlencode($path);
            }
        }

        $encodedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . implode('/', $pathArray);

        return $encodedUrl;
    }
}

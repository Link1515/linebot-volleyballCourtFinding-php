<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use LINE\Clients\MessagingApi\Model\LocationAction;
use LINE\Clients\MessagingApi\Model\QuickReply;
use LINE\Clients\MessagingApi\Model\QuickReplyItem;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\ActionType;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\TextMessageContent;
use TerryLin\LineBot\Helper;

class TextHandler implements HandlerInterface
{
    public function __construct(
        private readonly TextMessageContent $message
    ) {
    }

    public function getReplyMessages(): array
    {
        $text = $this->message->getText();

        if ($text === '球場資訊') {
            return $this->locationQuickReply();
        } elseif ($text === '使用教學') {
            return $this->sendTutorialMsg();
        }

        return [];
    }

    private function sendTutorialMsg()
    {
        return [
            new TextMessage([
                'type' => MessageType::TEXT,
                'text' => Helper::t('tutorial'),
            ])
        ];
    }

    private function locationQuickReply()
    {
        $quickReply = new QuickReply([
            'items' => [
                new QuickReplyItem([
                    'type'   => 'action',
                    'action' => new LocationAction([
                        'type'  => ActionType::LOCATION,
                        'label' => Helper::t('sendLocation')
                    ])
                ])
            ]
        ]);

        return [
            new TextMessage([
                'type'       => MessageType::TEXT,
                'text'       => Helper::t('sendLocationByButton'),
                'quickReply' => $quickReply
            ])
        ];
    }
}

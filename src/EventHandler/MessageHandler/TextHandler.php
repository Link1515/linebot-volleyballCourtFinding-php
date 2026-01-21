<?php

declare(strict_types=1);

namespace TerryLin\LineBot\EventHandler\MessageHandler;

use LINE\Clients\MessagingApi\Model\LocationAction;
use LINE\Clients\MessagingApi\Model\QuickReply;
use LINE\Clients\MessagingApi\Model\QuickReplyItem;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\ActionType;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\TextMessageContent;
use TerryLin\LineBot\EventHandler\EventHandlerInterface;

class TextHandler implements EventHandlerInterface
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
        $tutorialMsg = <<<'Msg'
        歡迎使用 超級排🏐球場 LINE 機器人

        點擊選單的 "球場資訊" 後，再點擊出現的 "傳送位置" 按鈕傳送自己所在的位置，機器人將會快速幫您找到附近最近的 5 個排球場!

        接著點擊想去的排球場，機器人就會傳送給您該球場的地圖，並根據球場所在的城市，提供天氣資訊!

        GitHub:
        https://github.com/Link1515/linebot-volleyballCourtFinding-php

        如果發現問題，歡迎透過 GitHub 聯繫我!
        Msg;

        return [
            new TextMessage([
                'type' => MessageType::TEXT,
                'text' => $tutorialMsg,
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
                        'label' => '傳送位置'
                    ])
                ])
            ]
        ]);

        return [
            new TextMessage([
                'type'       => MessageType::TEXT,
                'text'       => '請點下方的按鈕，傳送您的位置',
                'quickReply' => $quickReply
            ])
        ];
    }
}

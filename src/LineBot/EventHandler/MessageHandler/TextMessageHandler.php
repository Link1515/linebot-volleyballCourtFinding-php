<?php

declare(strict_types=1);

namespace Lynk\LineBot\EventHandler\MessageHandler;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\MessageEvent;
use Lynk\LineBot\EventHandler\EventHandlerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

class TextMessageHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly RequestInterface $req,
        private readonly MessageEvent $event
    ) {
    }

    public function handle(): void
    {
        $text = $this->event->getMessage()->getText();
        $replyToken = $this->event->getReplyToken();

        if ($text === '使用教學') {
            $this->sendTutorialMsg($replyToken);
        }
    }


    private function sendTutorialMsg(string $replyToken): void
    {
        $tutorialMsg = <<<'Msg'
        歡迎使用 超級排🏐球場 line機器人

        🔺點選選單中央的 "球場資訊" 後，再點擊出現的 "傳送位置" 按鈕傳送自己所在的位置，機器人將會快速幫您找到附近最近的5個排球場!

        接著點下想去的排球場，機器人就會傳送給您該球場的地圖，並且會根據您要去的城市，提供天氣資訊!

        github:
        https://github.com/Link1515/linebot-volleyballCourtFinding

        如果有出現 bug 歡迎透過 github 聯繫我
        Msg;

        $this->replyText($replyToken, $tutorialMsg);
    }

    private function replyText(string $replyToken, string $text)
    {
        $request = new ReplyMessageRequest([
            'replyToken' => $replyToken,
            'messages' => [(new TextMessage())->setText($text)->setType(MessageType::TEXT)],
        ]);

        try {
            $this->bot->replyMessage($request);
        } catch (ApiException $e) {
            $this->logger->info('BODY:' . $e->getResponseBody());
            throw $e;
        }
    }
}

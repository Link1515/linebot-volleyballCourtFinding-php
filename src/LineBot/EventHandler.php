<?php


declare(strict_types=1);

namespace Lynk\LineBot;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use LINE\Constants\MessageType;
use LINE\Clients\MessagingApi\ApiException;
use Psr\Log\LoggerInterface;

class EventHandler
{
  public function __construct(
    private readonly LoggerInterface $logger,
    private readonly MessagingApiApi $bot,
  ) {
  }


  public function handle(array $events): void
  {
    foreach ($events as $event) {
      if (!($event instanceof MessageEvent)) {
        $this->logger->info('Non message event has come');
        continue;
      }

      $message = $event->getMessage();
      if (!($message instanceof TextMessageContent)) {
        $this->logger->info('Non text message has come');
        continue;
      }

      $replyText = $message->getText();

      try {
        $this->bot->replyMessage(new ReplyMessageRequest([
          'replyToken' => $event->getReplyToken(),
          'messages' => [(new TextMessage())->setText($replyText)->setType(MessageType::TEXT)],
        ]));
      } catch (ApiException $e) {
        $this->logger->error($e->getCode() . ' ' . $e->getResponseBody());
      } catch (\Throwable $th) {
        $this->logger->error($th);
      }
    }
  }
}

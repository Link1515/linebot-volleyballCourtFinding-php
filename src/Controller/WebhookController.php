<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Controller;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Constants\HTTPHeader;
use LINE\Parser\EventRequestParser;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use TerryLin\LineBot\EventHandler;
use TerryLin\LineBot\Settings;

class WebhookController
{
    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly Settings $settings,
        private readonly EventHandler $eventHandler
    ) {
    }

    public function __invoke(Request $req, Response $res): Response
    {
        try {
            // send response immediately by fastcgi_finish_request
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }

            $secret       = $this->settings->get('bot.channelSecret');
            $body         = $req->getBody()->getContents();
            $parsedEvents = EventRequestParser::parseEventRequest($body, $secret, $signature[0]);

            $this->eventHandler->handle($parsedEvents->getEvents());

            return $res->withStatus(200, 'OK');
        } catch (InvalidSignatureException $e) {
            return $res->withStatus(400, 'Invalid signature');
        } catch (InvalidEventRequestException $e) {
            return $res->withStatus(400, "Invalid event request");
        } catch (ApiException $e) {
            $this->logger->error($e->getMessage());
            return $res->withStatus(400, "Invalid event request");
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            return $res->withStatus(500, 'Internal Server Error');
        }
    }
}

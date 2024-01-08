<?php

namespace Lynk\LineBot;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Constants\HTTPHeader;
use LINE\Parser\EventRequestParser;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Route
{
    public function register(\Slim\App $app)
    {
        $app->post('/callback', function (RequestInterface $req, ResponseInterface $res) {
            /** @var \LINE\Clients\MessagingApi\Api\MessagingApiApi $bot */
            $bot = $this->get(MessagingApiApi::class);
            /** @var \Psr\Log\LoggerInterface logger */
            $logger = $this->get(\Psr\Log\LoggerInterface::class);

            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }

            // Check request with signature and parse request
            try {
                $secret = $this->get('settings')['bot']['channelSecret'];
                $parsedEvents = EventRequestParser::parseEventRequest($req->getBody(), $secret, $signature[0]);
            } catch (InvalidSignatureException $e) {
                return $res->withStatus(400, 'Invalid signature');
            } catch (InvalidEventRequestException $e) {
                return $res->withStatus(400, "Invalid event request");
            }

            /** @var EventHandler $eventHandler */
            $eventHandler = $this->get(EventHandler::class);
            $eventHandler->handle($parsedEvents->getEvents());

            $res->withStatus(200, 'OK');
            return $res;
        });

        $app->get('/', function (RequestInterface $req, ResponseInterface $res) {
            $res->getBody()->write('hello line');

            return $res;
        });
    }
}

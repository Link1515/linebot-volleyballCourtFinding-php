<?php

declare(strict_types=1);

namespace Lynk\LineBot\EventHandler\MessageHandler;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\LocationMessageContent;
use Lynk\LineBot\BotUtils;
use Lynk\LineBot\EventHandler\EventHandlerInterface;
use Lynk\LineBot\EventHandler\MessageHandler\Flex\FlexSampleSportsField;
use Lynk\LineBot\Model\SportsFieldInfo;
use Psr\Log\LoggerInterface;

class LocationMessageHandler implements EventHandlerInterface
{
    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly MessageEvent $event
    ) {
    }
    public function handle(): void
    {
        $AMOUNT_OF_FIELD = 5;
        $MAX_DISTANCE = 15; // 15 km

        /** @var LocationMessageContent $locationMessage */
        $locationMessage = $this->event->getMessage();
        $userLocation = [$locationMessage->getLatitude(), $locationMessage->getLongitude()];

        $sportsFieldInfoListFile = __DIR__ . '/../../../../data/sportsFieldInfoList.json';
        if (!file_exists($sportsFieldInfoListFile)) {
            include_once __DIR__ . '/../../../../scripts/fetchSportsFieldInfoList.php';
        }


        $sportsFieldInfoList =
            json_decode(file_get_contents($sportsFieldInfoListFile));

        /** @var SportsFieldInfo $sportsFieldInfo */
        foreach ($sportsFieldInfoList as $sportsFieldInfo) {
            $targetLocation = [
                (float) explode(',', $sportsFieldInfo->LatLng)[0],
                (float) explode(',', $sportsFieldInfo->LatLng)[1]
            ];

            $distance = $this->calculateDistance($userLocation, $targetLocation);

            $sportsFieldInfo->Distance = round($distance, 2);
        }

        usort($sportsFieldInfoList, function ($a, $b) {
            return $a->Distance <=> $b->Distance;
        });

        $result = array_slice($sportsFieldInfoList, 0, 5);
        $result = array_filter($result, function ($sportField) use ($MAX_DISTANCE) {
            return $sportField->Distance < $MAX_DISTANCE;
        });

        if (count($result) === 0) {
            $botRequest = BotUtils::createTextReplyRequest($this->event->getReplyToken(), '附近沒有球場');
            $this->bot->replyMessage($botRequest);
            return;
        }

        $flexMessage = FlexSampleSportsField::get($result);
        $botRequest = BotUtils::createMessageReplyRequest($this->event->getReplyToken(), $flexMessage);

        try {
            $this->bot->replyMessage($botRequest);
        } catch (ApiException $e) {
            $this->logger->error('BODY:' . $e->getResponseBody());
            throw $e;
        }
    }


    /**
     * Calculates the distance between two locations based on their latitude and longitude.
     *
     * This function uses the Haversine formula to compute the great-circle distance
     * between two points on the surface of a sphere, given their latitude and longitude
     * coordinates.
     *
     * @param array $location1 An array representing the coordinates of the first location.
     *                          $location1[0] represents the latitude, and $location1[1] represents the longitude.
     * @param array $location2 An array representing the coordinates of the second location.
     *                          $location2[0] represents the latitude, and $location2[1] represents the longitude.
     *
     * @return float Distance between two locations
     */
    private function calculateDistance(array $location1, array $location2): float
    {
        $lat1 = $location1[0];
        $lon1 = $location1[1];
        $lat2 = $location2[0];
        $lon2 = $location2[1];

        if ($lat1 === $lat2 && $lon1 === $lon2) {
            return 0;
        } else {
            $radlat1 = (pi() * $lat1) / 180;
            $radlat2 = (pi() * $lat2) / 180;
            $theta = $lon1 - $lon2;
            $radtheta = (pi() * $theta) / 180;
            $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);
            if ($dist > 1) {
                $dist = 1;
            }
            $dist = acos($dist);
            $dist = ($dist * 180) / pi();
            $dist = $dist * 60 * 1.1515;
            $dist = $dist * 1.609344;

            return $dist;
        }
    }
}

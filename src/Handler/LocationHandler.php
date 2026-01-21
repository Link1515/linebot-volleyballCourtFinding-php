<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\LocationMessageContent;
use TerryLin\LineBot\BotUtils;
use TerryLin\LineBot\Flex\SportFieldsFlex;
use TerryLin\LineBot\Model\SportsFieldInfo;

class LocationHandler implements HandlerInterface
{
    public function __construct(
        private readonly LocationMessageContent $message
    ) {
    }

    public function getReplyMessages(): array
    {
        $AMOUNT_OF_FIELD = 5;
        $MAX_DISTANCE    = 15; // 15 km

        $userLocation = [$this->message->getLatitude(), $this->message->getLongitude()];

        $sportsFieldInfoList = BotUtils::getSportsFieldInfoList();

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

        $result = array_slice($sportsFieldInfoList, 0, $AMOUNT_OF_FIELD);
        $result = array_filter($result, function ($sportField) use ($MAX_DISTANCE) {
            return $sportField->Distance < $MAX_DISTANCE;
        });

        if (count($result) === 0) {
            return [
                $textMessage = new TextMessage([
                    'type' => MessageType::TEXT,
                    'text' => '附近沒有球場',
                ])
            ];
        }

        $flexMessage = SportFieldsFlex::get($result);
        $textMessage = new TextMessage([
            'type' => MessageType::TEXT,
            'text' => '請點選您想去的球場',
        ]);

        return [$flexMessage, $textMessage];
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
            $radlat1  = (pi() * $lat1) / 180;
            $radlat2  = (pi() * $lat2) / 180;
            $theta    = $lon1 - $lon2;
            $radtheta = (pi() * $theta) / 180;
            $dist     = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);
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

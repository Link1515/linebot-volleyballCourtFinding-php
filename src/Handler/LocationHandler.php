<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\LocationMessageContent;
use TerryLin\LineBot\Flex\CourtsFlex;
use TerryLin\LineBot\Helper;
use TerryLin\LineBot\Model\Court;

class LocationHandler implements HandlerInterface
{
    private const AMOUNT_OF_FIELD = 5;
    private const MAX_DISTANCE    = 15; // km

    public function __construct(
        private readonly LocationMessageContent $message
    ) {
    }

    public function getReplyMessages(): array
    {
        $nearbyCourts = $this->getNearbyCourts();

        if (count($nearbyCourts) === 0) {
            return [
                $textMessage = new TextMessage([
                    'type' => MessageType::TEXT,
                    'text' => Helper::t('noCourtAround'),
                ])
            ];
        }

        $flexMessage = CourtsFlex::get($nearbyCourts);
        $textMessage = new TextMessage([
            'type' => MessageType::TEXT,
            'text' => Helper::t('selectCourt'),
        ]);

        return [$flexMessage, $textMessage];
    }

    private function getNearbyCourts()
    {
        $userLocation = [$this->message->getLatitude(), $this->message->getLongitude()];

        $courts = Helper::getCourts();

        /** @var Court $court */
        foreach ($courts as $court) {
            $targetLocation = [
                (float) explode(',', $court->LatLng)[0],
                (float) explode(',', $court->LatLng)[1]
            ];

            $distance = $this->calculateDistance($userLocation, $targetLocation);

            $court->Distance = round($distance, 2);
        }

        usort($courts, function ($a, $b) {
            return $a->Distance <=> $b->Distance;
        });

        $result = array_slice($courts, 0, self::AMOUNT_OF_FIELD);
        $result = array_filter($result, function ($court) {
            return $court->Distance < self::MAX_DISTANCE;
        });
        return $result;
    }

    private const EARTH_RADIUS = [
        'km' => 6371.0088,
        'm'  => 6371008.8,
        'nm' => 3440.0695, // nautical mile
    ];

    private function calculateDistance(array $a, array $b, string $unit = 'km'): float
    {
        $lat1 = $this->toRad($a[0]);
        $lat2 = $this->toRad($b[0]);
        $dLat = $lat2 - $lat1;
        $dLon = $this->toRad($b[1]) - $this->toRad($a[1]);

        // Harversine
        $sinDLat = sin($dLat / 2);
        $sinDLon = sin($dLon / 2);
        $h       = $sinDLat * $sinDLat + cos($lat1) * cos($lat2) * $sinDLon * $sinDLon;

        $clampedH = min(1, max(0, $h));
        $c        = 2 * asin(sqrt($clampedH));

        return self::EARTH_RADIUS[$unit] * $c;
    }

    private function toRad(float $deg): float
    {
        return ($deg * M_PI) / 180.0;
    }
}

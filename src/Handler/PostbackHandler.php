<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

use GuzzleHttp\Client;
use LINE\Clients\MessagingApi\Model\LocationMessage;
use LINE\Clients\MessagingApi\Model\Message;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\PostbackEvent;
use TerryLin\LineBot\BotUtils;
use TerryLin\LineBot\Model\Court;

class PostbackHandler implements HandlerInterface
{
    private readonly Client $httpClient;

    public function __construct(
        private readonly PostbackEvent $event
    ) {
        $this->httpClient = new Client();
    }

    public function getReplyMessages(): array
    {
        parse_str($this->event->getPostback()->getData(), $data);
        $GymID = (int) $data['GymID'];

        $courts = BotUtils::getCourts();

        /** @var Court $court */
        foreach ($courts as $court) {
            if ($court->GymID === $GymID) {
                $city       = mb_substr($court->Address, 0, 3);
                $weacherMsg = $this->getWeatherMsg($city);

                return [
                    new LocationMessage([
                        'type'      => MessageType::LOCATION,
                        'title'     => $court->Name,
                        'address'   => $court->Address,
                        'latitude'  => (float) explode(',', $court->LatLng)[0],
                        'longitude' => (float) explode(',', $court->LatLng)[1],
                    ]),
                    $weacherMsg
                ];
            }
        }

        return [
            new TextMessage([
                'type' => MessageType::TEXT,
                'text' => '找不到這個地點',
            ])
        ];
    }

    private function getWeatherMsg(string $city): Message
    {
        $cityConvertList = ['彰化市', '嘉義市', '花蓮市'];

        if (in_array($city, $cityConvertList)) {
            $city = str_replace('市', '縣', $city);
        }

        $res = $this->httpClient->request('GET', 'https://opendata.cwa.gov.tw/api/v1/rest/datastore/F-C0032-001', [
            'query' => [
                'Authorization' => $_ENV['WEATHER_API_KEY'],
                'locationName'  => $city
            ]
        ]);
        $weatherData = json_decode($res->getBody()->getContents(), true)['records']['location'][0]['weatherElement'];

        $precipitation          = (int) $weatherData[1]['time'][0]['parameter']['parameterName'];
        $precipitationAlertIcon = $precipitation > 60 ? '⚠️' : '';
        $minTemperature         = $weatherData[2]['time'][0]['parameter']['parameterName'];
        $discription            = $weatherData[3]['time'][0]['parameter']['parameterName'];
        $maxTemperature         = $weatherData[4]['time'][0]['parameter']['parameterName'];

        $text = <<<Text
        🌡{$city}今日{$discription}
        🔺️️最高溫: {$maxTemperature} 度
        ️️️🔺️最低溫: {$minTemperature} 度 
        🔺️降雨機率: {$precipitationAlertIcon} {$precipitation}% {$precipitationAlertIcon}
        Text;

        return new TextMessage([
            'type' => MessageType::TEXT,
            'text' => $text,
        ]);
    }
}

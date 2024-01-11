<?php

declare(strict_types=1);

namespace Lynk\LineBot\EventHandler;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\LocationMessage;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\MessageType;
use LINE\Webhook\Model\PostbackEvent;
use Lynk\LineBot\BotUtils;
use Lynk\LineBot\Model\SportsFieldInfo;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;

class PostbackEventHandler implements EventHandlerInterface
{

    public function __construct(
        private readonly MessagingApiApi $bot,
        private readonly LoggerInterface $logger,
        private readonly PostbackEvent $event
    ) {
    }

    public function handle(): void
    {
        parse_str($this->event->getPostback()->getData(), $data);
        $GymID = (int) $data['GymID'];

        $sportsFieldInfoList = BotUtils::getSportsFieldInfoList();

        /** @var SportsFieldInfo $sportsFieldInfo */
        foreach ($sportsFieldInfoList as $sportsFieldInfo) {
            if ($sportsFieldInfo->GymID === $GymID) {
                $city = mb_substr($sportsFieldInfo->Address, 0, 3);
                $weacherMsg = $this->getWeatherMsg($city);

                $botRequest = BotUtils::createMessageReplyRequest($this->event->getReplyToken(), [
                    new LocationMessage([
                        'type' => MessageType::LOCATION,
                        'title' => $sportsFieldInfo->Name,
                        'address' => $sportsFieldInfo->Address,
                        'latitude' => (float) explode(',', $sportsFieldInfo->LatLng)[0],
                        'longitude' => (float) explode(',', $sportsFieldInfo->LatLng)[1],
                    ]),
                    new TextMessage([
                        'type' => MessageType::TEXT,
                        'text' => $weacherMsg,
                    ]),
                ]);

                $this->bot->replyMessage($botRequest);

                return;
            }
        }
    }

    private function getWeatherMsg(string $city): string
    {

        $cityConvertList = ['彰化市', '嘉義市', '花蓮市'];

        if (in_array($city, $cityConvertList)) {
            $city = str_replace('市', '縣', $city);
        }

        $client = new Client();
        $res = $client->request('GET', 'https://opendata.cwa.gov.tw/api/v1/rest/datastore/F-C0032-001', [
            'query' => [
                'Authorization' => $_ENV['WEATHER_API_KEY'],
                'locationName' => $city
            ]
        ]);
        $weatherData = json_decode($res->getBody()->getContents(), true)['records']['location'][0]['weatherElement'];

        $precipitation = (int) $weatherData[1]['time'][0]['parameter']['parameterName'];
        $precipitationAlertIcon = $precipitation > 60 ? '⚠️' : '';
        $minTemperature = $weatherData[2]['time'][0]['parameter']['parameterName'];
        $discription = $weatherData[3]['time'][0]['parameter']['parameterName'];
        $maxTemperature = $weatherData[4]['time'][0]['parameter']['parameterName'];

        return <<<Msg
        {$city}今日{$discription}
        🔺️️最高溫: {$maxTemperature} 度
        ️️️🔺️最低溫: {$minTemperature} 度 
        🔺️降雨機率: {$precipitationAlertIcon} {$precipitation}% {$precipitationAlertIcon}
        Msg;
    }
}

<?php

namespace Lynk\LineBot;

class Setting
{
    public static function getSetting()
    {
        return [
            'settings' => [
                'displayErrorDetails' => true, // set to false in production

                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/../../logs/app.log',
                ],

                'bot' => [
                    'channelToken' => $_ENV['LINEBOT_CHANNEL_TOKEN'] ?: '',
                    'channelSecret' => $_ENV['LINEBOT_CHANNEL_SECRET'] ?: '',
                ],
            ],
        ];
    }
}

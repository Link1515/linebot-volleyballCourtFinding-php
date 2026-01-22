<?php

declare(strict_types=1);

namespace TerryLin\LineBot;

class Helper
{
    private const COURTS_JSON_FILE    = __DIR__ . '/../storage/data/courts.json';
    private const FETCH_COURTS_SCRIPT = __DIR__ . '/../scripts/fetchCourts.php';
    private const MESSAGES_FILE       = __DIR__ . '/../app/messages.php';

    public static function getCourts(): array
    {
        static $courst = [];

        if (empty($courst)) {
            if (!file_exists(self::COURTS_JSON_FILE)) {
                include_once self::FETCH_COURTS_SCRIPT;
            }

            $courst = json_decode(file_get_contents(self::COURTS_JSON_FILE)) ?? [];
        }

        return $courst;
    }

    public static function encodeUrlPath(string $url): string
    {
        $parsedUrl = parse_url($url);

        $pathArray = [];
        if (array_key_exists('path', $parsedUrl)) {
            $pathArray = explode('/', $parsedUrl['path']);
            foreach ($pathArray as &$path) {
                $path = urlencode($path);
            }
        }

        $encodedUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . implode('/', $pathArray);

        return $encodedUrl;
    }

    public static function t(string $key, array $params = []): string
    {
        static $dict = [];

        if (empty($dict)) {
            $dict = require self::MESSAGES_FILE;
        }

        $text = $dict[$key] ?? $key;
        foreach ($params as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }

        return $text;
    }
}

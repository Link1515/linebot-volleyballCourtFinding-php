<?php

declare(strict_types=1);

namespace TerryLin\LineBot;

class Helper
{
    private const COURTS_JSON_FILE    = __DIR__ . '/../storage/data/courts.json';
    private const FETCH_COURTS_SCRIPT = __DIR__ . '/../scripts/fetchCourts.php';
    private static $courst            = [];

    public static function getCourts(): array
    {
        if (empty(self::$courst)) {
            if (!file_exists(self::COURTS_JSON_FILE)) {
                include_once self::FETCH_COURTS_SCRIPT;
            }

            self::$courst = json_decode(file_get_contents(self::COURTS_JSON_FILE));
        }

        return self::$courst;
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
}

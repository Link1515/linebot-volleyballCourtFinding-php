<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$client = new GuzzleHttp\Client();
$apiUrl = 'https://iplay.sa.gov.tw/api/GymSearchAllList?$format=application/json;odata.metadata=none&Keyword=%E6%8E%92%E7%90%83%E5%A0%B4';
$dirPath = __DIR__ . '/../data';
$filename = 'sportsFieldInfoList.json';

$res = $client->request('GET', $apiUrl);

if (!is_dir($dirPath)) {
    mkdir($dirPath);
}

file_put_contents($dirPath . '/' . $filename, $res->getBody()->getContents());

echo 'ok';

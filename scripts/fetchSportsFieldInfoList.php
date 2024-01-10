<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$client = new GuzzleHttp\Client();
$apiUrl = 'https://iplay.sa.gov.tw/api/GymSearchAllList?$format=application/json;odata.metadata=none&Keyword=%E6%8E%92%E7%90%83%E5%A0%B4';
$filePath = __DIR__ . '/../data/sportsFieldInfoList.json';

$res = $client->request('GET', $apiUrl);

file_put_contents($filePath, $res->getBody()->getContents());

echo 'ok';

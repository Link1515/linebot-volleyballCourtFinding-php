<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$client   = new GuzzleHttp\Client();
$apiUrl   = 'https://iplay.sports.gov.tw/api/GymSearchAllList?$format=application/json;odata.metadata=none&Keyword=排球場';
$dirPath  = __DIR__ . '/../storage/data';
$filename = 'courts.json';

$res = $client->request('GET', $apiUrl);

if (!is_dir($dirPath)) {
    mkdir($dirPath);
}

$resData = json_decode($res->getBody()->getContents(), true);

$filteredData = array_filter($resData, function ($data) {
    return $data['OpenState'] !== 'N' && $data['RentState'] !== '不開放對外場地租借';
});
$filteredData = array_values($filteredData);

file_put_contents($dirPath . '/' . $filename, json_encode($filteredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo 'ok';

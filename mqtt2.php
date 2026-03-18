<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require "./config.php";
require "../vendor/autoload.php";

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\ConnectionSettings;

$username = $clientId;
$password = $key;

$startPrintJson = '
    {
  "id": 100015,
  "method": 1020,
  "params": {
    "config": {
      "bedlevel_force": false,
      "delay_video": false,
      "print_layout": "A",
      "printer_check": false,
      "slot_map": [
        {
          "canvas_id": 0,
          "t": 0,
          "tray_id": 1
        }
      ]
    },
    "filename": "' . $file . '",
    "storage_media": "local"
  }
}';
print_r2(json_decode($startPrintJson, true));

$printAllFilesJson = '{
  "method": 1044,
  "params": {
    "storage_media": "local",
    "offset": 0,
    "limit": 20
  },
  "id": 9224526
}';

$connectionSettingsObj  = new ConnectionSettings();
$connectionSettings = $connectionSettingsObj
    ->setUsername($username)
    ->setPassword($password);

$mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
$mqtt->connect($connectionSettings, true);

$registerId = 'php';
$registerJson = array(
    "request_id" => $registerId,
    "client_id" => $registerId
);

// Register client
$mqtt->publish('elegoo/' . $serialNo3d .'/api_register', json_encode($registerJson), 0);
sleep(2);
// Do command


$mqtt->publish('elegoo/' . $serialNo3d . '/' . $registerId . '/api_request', $startPrintJson, 0);



$mqtt->disconnect();





echo("abc :: $serialNo3d");




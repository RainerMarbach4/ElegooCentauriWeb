<?php
/**
 * mqtt_dump.php - Debug script to listen to all MQTT traffic from the printer
 */

require_once '../config_elegoo.php';
require_once 'printer_api.php';

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\ConnectionSettings;

$config = [
    'server' => $server, 'port' => $port, 'clientId' => $clientId, 'key' => $key, 'serialNo3d' => $serialNo3d
];

try {
    echo "Connecting to {$server} and listening to ALL topics (#)...\n";
    echo "Using MQTT User: {$clientId} (from config)\n\n";

    // Use the exact clientId from config as MQTT ID, as in mqtt2.php
    $mqtt = new MqttClient($server, (int)$port, $clientId);
    
    $connectionSettings = (new ConnectionSettings())
        ->setUsername($clientId)
        ->setPassword($key);

    $mqtt->connect($connectionSettings, true);

    $mqtt->subscribe('#', function ($topic, $message) {
        echo sprintf("[%s] %s\n", $topic, $message);
    }, 0);

    echo "Connection successful. Waiting for messages...\n";

    $start = time();
    while (time() - $start < 15) {
        $mqtt->loop(true, true);
        usleep(200000);
    }

    $mqtt->disconnect();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

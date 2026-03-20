<?php
/**
 * test_printer.php - Example of using the ElegooPrinter API
 */

require_once '../config_elegoo.php';
require_once 'printer_api.php';

// Prepare config from global variables
$config = [
    'server' => $server,
    'port' => $port,
    'clientId' => $clientId,
    'key' => $key,
    'serialNo3d' => $serialNo3d
];

try {
    $printer = new ElegooPrinter($config);
    echo "Connecting to printer at {$server}...\n";
    $printer->connect();

    echo "Requesting Status (Method 1003)...\n";
    $printer->getStatus();

    echo "Requesting File List (Method 1044)...\n";
    $printer->getFileList();

    echo "Commands sent. Check MQTT broker for responses on 'elegoo/{$serialNo3d}/.../api_response'.\n";

    $printer->disconnect();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

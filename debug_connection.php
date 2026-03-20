<?php
/**
 * debug_connection.php - Step-by-step debug of the MQTT process
 */

require_once '../config_elegoo.php';
require_once 'printer_api.php';

$config = [
    'server' => $server, 'port' => $port, 'clientId' => $clientId, 'key' => $key, 'serialNo3d' => $serialNo3d
];

echo "--- MQTT DEBUG START ---\n";

try {
    $printer = new ElegooPrinter($config);
    
    echo "1. Connecting to MQTT Broker...\n";
    $printer->connect();
    echo "   DONE (Registered as php_elegoo_manager)\n";

    echo "2. Sending GET_STATUS (Method 1002)...\n";
    $response = $printer->getStatus();
    
    if ($response) {
        echo "   SUCCESS! Received response:\n";
        print_r($response);
    } else {
        echo "   TIMEOUT! No response received from printer within timeout.\n";
    }

    $printer->disconnect();
    echo "4. Disconnected.\n";

} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "--- MQTT DEBUG END ---\n";

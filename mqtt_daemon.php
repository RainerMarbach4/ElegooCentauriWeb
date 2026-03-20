<?php
/**
 * mqtt_daemon.php - Background worker to cache printer status
 * Run this via CLI: php mqtt_daemon.php
 */

require_once '../config_elegoo.php';
require_once 'printer_api.php';

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\ConnectionSettings;

$statusFile = __DIR__ . '/current_status.json';
$state = [
    'last_update' => 0,
    'result' => [],
    'files' => []
];

// Load existing state if available
if (file_exists($statusFile)) {
    $state = json_decode(file_get_contents($statusFile), true) ?: $state;
}

try {
    echo "Starting MQTT Daemon for {$server}...\n";
    
    $mqtt = new MqttClient($server, (int)$port, "php_daemon_" . uniqid());
    $settings = (new ConnectionSettings())->setUsername($clientId)->setPassword($key);
    $mqtt->connect($settings, true);

    // Register
    $reg = ["request_id" => "daemon_reg", "client_id" => "php_elegoo_manager"];
    $mqtt->publish("elegoo/{$serialNo3d}/api_register", json_encode($reg), 0);

    // Subscribe to Status Pushes (Method 6000) and API Responses
    $mqtt->subscribe("elegoo/{$serialNo3d}/#", function ($topic, $message) use (&$state, $statusFile, $serialNo3d) {
        $data = json_decode($message, true);
        if (!$data) return;

        $updated = false;

        // 1. Handle Automatic Status Pushes (Temperatures etc)
        if (strpos($topic, 'api_status') !== false && isset($data['result'])) {
            $state['result'] = array_merge_recursive($state['result'], $data['result']);
            $updated = true;
        }

        // 2. Handle Direct API Responses (Status 1002, File List 1044)
        if (strpos($topic, 'api_response') !== false && isset($data['result'])) {
            if (isset($data['result']['files'])) {
                $state['files'] = $data['result']['files'];
            }
            $state['result'] = array_merge($state['result'], $data['result']);
            $updated = true;
        }

        if ($updated) {
            $state['last_update'] = time();
            file_put_contents($statusFile, json_encode($state));
            echo "State updated: " . date('H:i:s') . " (Topic: $topic)\n";
        }
    }, 0);

    echo "Listening for printer data...\n";

    // Keep running
    $lastPing = 0;
    while (true) {
        $mqtt->loop(true);
        
        // Send a status request every 30 seconds to keep connection alive and get full info
        if (time() - $lastPing > 30) {
            $req = ["id" => rand(1000, 9999), "method" => 1002, "params" => (object)[]];
            $mqtt->publish("elegoo/{$serialNo3d}/php_elegoo_manager/api_request", json_encode($req), 0);
            $lastPing = time();
        }
        
        usleep(100000);
    }

} catch (Exception $e) {
    echo "Daemon Error: " . $e->getMessage() . "\n";
    exit(1);
}

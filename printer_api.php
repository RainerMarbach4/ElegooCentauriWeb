<?php
/**
 * ElegooPrinter - A PHP Class for controlling Elegoo Centauri Carbon 2 via MQTT
 */

require_once __DIR__ . '/../vendor/autoload.php';

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\ConnectionSettings;

class ElegooPrinter {
    private $server;
    private $port;
    private $clientId;
    private $key;
    private $serialNo3d;
    private $mqtt;
    private $appId;

    public function __construct($config) {
        $this->server = $config['server'];
        $this->port = $config['port'];
        $this->clientId = $config['clientId'];
        $this->key = $config['key'];
        $this->serialNo3d = $config['serialNo3d'];
        // This ID worked in the successful MQTT Dump
        $this->appId = "php_elegoo_manager";
    }

    public function connect() {
        $connectionSettings = (new ConnectionSettings())
            ->setUsername($this->clientId)
            ->setPassword($this->key);

        // Random connection ID to avoid socket EOF/Conflicts
        $mqttConnectionId = "php_session_" . rand(1000, 9999);
        
        $this->mqtt = new MqttClient($this->server, (int)$this->port, $mqttConnectionId);
        $this->mqtt->connect($connectionSettings, true);

        // Register client
        $registerJson = [
            "request_id" => "reg_" . time(),
            "client_id" => $this->appId
        ];
        $this->publish('api_register', $registerJson);
        
        // Wait longer for the printer to process registration
        usleep(800000); 
    }

    public function disconnect() {
        if ($this->mqtt) {
            $this->mqtt->disconnect();
        }
    }

    private function publish($suffix, $payload) {
        $topic = "elegoo/{$this->serialNo3d}/{$this->appId}/{$suffix}";
        if ($suffix === 'api_register') {
            $topic = "elegoo/{$this->serialNo3d}/api_register";
        }
        $json = is_array($payload) ? json_encode($payload) : $payload;
        $this->mqtt->publish($topic, $json, 0);
    }

    /**
     * Just send a command without waiting.
     * Extremely fast for web requests.
     */
    private function sendOnly($methodId, $params = []) {
        $requestId = rand(1000000, 9999999);
        $payload = [
            "id" => $requestId,
            "method" => (int)$methodId,
            "params" => $params ?: (object)[]
        ];
        $this->publish('api_request', $payload);
        return $requestId;
    }

    // --- Helper Methods for common actions ---

    public function getStatus() {
        return $this->sendOnly(1002);
    }

    public function getFileList($offset = 0, $limit = 100) {
        return $this->sendOnly(1044, [
            "storage_media" => "local",
            "offset" => $offset,
            "limit" => $limit
        ]);
    }

    public function deleteFile($filename) {
        return $this->sendOnly(1047, [
            "filename" => $filename,
            "storage_media" => "local"
        ]);
    }

    public function startPrint($filename, $slot = 1, $config = []) {
        $defaultConfig = [
            "bedlevel_force" => false,
            "delay_video" => false,
            "print_layout" => "A",
            "printer_check" => false,
            "slot_map" => [
                [
                    "canvas_id" => 0, 
                    "t" => 0, 
                    "tray_id" => (int)$slot
                ]
            ]
        ];
        return $this->sendOnly(1020, [
            "filename" => $filename,
            "storage_media" => "local",
            "config" => array_merge($defaultConfig, $config)
        ]);
    }

    public function pausePrint() {
        return $this->sendMethod(1021);
    }

    public function resumePrint() {
        return $this->sendMethod(1023);
    }

    public function cancelPrint() {
        return $this->sendMethod(1022);
    }
}

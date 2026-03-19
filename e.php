<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require ('../config_elegoo.php');

function uploadFilePut($filePath, $host, $token, $fileNameHeader = null)
{
    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }

    $fileBytes = file_get_contents($filePath);
    $fileLength = strlen($fileBytes);
    $md5Hash = md5($fileBytes);

    if ($fileNameHeader === null) {
        $fileNameHeader = basename($filePath);
    }
    $headers = [
        "User-Agent: ElegooLink/0.0.1",
        "Accept: application/json",
        "Content-Type: application/octet-stream",
        "Content-Length: $fileLength",
        "Content-Range: bytes 0-" . ($fileLength - 1) . "/$fileLength",
        "X-File-Name: $fileNameHeader",
        "X-File-MD5: $md5Hash",
        "X-Token: $token"
    ];

    $ch = curl_init($host . "/upload");

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileBytes);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error: $error");
    }

    curl_close($ch);

    return [
        "status" => $statusCode,
        "response" => $response
    ];
}



try {
    $result = uploadFilePut($file, $server, $key);
    if ($result['status'] >= 200 && $result['status'] < 300) {
        echo "Success! $file uploaded to $server\n";
    } else {
        echo "Upload failed with status code: " . $result['status'] . "\n";
        echo "Response: " . $result['response'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
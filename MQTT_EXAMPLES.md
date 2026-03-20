# Elegoo Centauri Carbon 2 - MQTT Method Examples

This document provides JSON payload examples for all MQTT methods used by the Elegoo Centauri Carbon 2.

## Storage Media Options
For all methods using `storage_media`, the following values are typically valid:
- `"local"`: Internal printer storage.
- `"usb"`: USB flash drive connected to the printer.
- `"sdcard"`: SD card (if supported by hardware).
- `"cloud"`: Files synced from the Elegoo Cloud.

---

## 1. System Information & Status

### Method 1001: GET_SYSTEM_INFO
Retrieves hardware and firmware version info.
```json
{
  "id": 123,
  "method": 1001,
  "params": {}
}
```

### Method 1003: GET_STATUS
Gets real-time printer status (temps, fans, progress).
```json
{
  "id": 124,
  "method": 1003,
  "params": {}
}
```

---

## 2. Print Control

### Method 1020: START_PRINT (Confirmed)
Starts a specific print job.
```json
{
  "id": 100015,
  "method": 1020,
  "params": {
    "filename": "example.gcode",
    "storage_media": "local",
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
    }
  }
}
```

### Method 1021: PAUSE_PRINT
Pauses the current print job.
```json
{
  "id": 125,
  "method": 1021,
  "params": {}
}
```

### Method 1022: CANCEL_PRINT
Stops and cancels the current print.
```json
{
  "id": 126,
  "method": 1022,
  "params": {}
}
```

---

## 3. Hardware Control

### Method 1027: CTRL_MOVE
Moves printer axes manually.
```json
{
  "id": 127,
  "method": 1027,
  "params": {
    "x": 10.0,
    "y": 0.0,
    "z": 5.0,
    "e": 0.0,
    "speed": 3000
  }
}
```

### Method 1028: SET_TEMPTURE
Sets nozzle or bed temperature.
```json
{
  "id": 128,
  "method": 1028,
  "params": {
    "type": "extruder",
    "val": 210
  }
}
```

### Method 1030: CTRL_FAN
Controls fan speed (0-255).
```json
{
  "id": 129,
  "method": 1030,
  "params": {
    "id": 1,
    "speed": 255
  }
}
```

---

## 4. File Management

### Method 1044: GET_FILE_LIST (Confirmed)
Lists files in a specific storage media.
```json
{
  "id": 9224526,
  "method": 1044,
  "params": {
    "storage_media": "local",
    "offset": 0,
    "limit": 20
  }
}
```

### Method 1047: DEL_FILE
Deletes a file from storage.
```json
{
  "id": 130,
  "method": 1047,
  "params": {
    "filename": "old_file.gcode",
    "storage_media": "local"
  }
}
```

---

## 5. Canvas (Multicolor System)

### Method 2005: CANVAS_GET_CHANNEL_INFO
Gets info about filament channels.
```json
{
  "id": 131,
  "method": 2005,
  "params": {}
}
```

---

## 6. Discovery

### Method 7000: SEARCH_BRODCAST
Trigger discovery broadcast (usually via UDP port 3000).
```json
{
  "id": 132,
  "method": 7000,
  "params": {}
}
```

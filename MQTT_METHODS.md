# Elegoo Centauri Carbon 2 - MQTT Methods Documentation (Official)

This document lists the official MQTT method IDs and their descriptions for the Elegoo Centauri Carbon 2, extracted from the `elegoo/common/method.h` source file.

## Core Control Methods

| Method ID | Command | Description |
| :--- | :--- | :--- |
| **1001** | `GET_SYSTEM_INFO` | Get system information |
| **1002** | `GET_BASE_INFO` | Get base information |
| **1003** | `GET_STATUS` | Get current printer status |
| **1004** | `GET_FAN_STATUS` | Get fan status |
| **1005** | `GET_PRINTS_INFO` | Get information about current prints |
| **1006** | `GET_HOME_STATUS` | Get homing status |
| **1007** | `EMERGENCY_STOP` | Trigger an emergency stop |
| **1019** | `SET_PRINT_CFG` | Set printing configuration |
| **1020** | `START_PRINT` | Start a print job |
| **1021** | `PAUSE_PRINT` | Pause the current print (Note: label in source is 'stop print') |
| **1022** | `CANCEL_PRINT` | Cancel/Stop the current print |
| **1023** | `RESUME_PRINT` | Resume a paused print |
| **1024** | `LOAD_FILAMENT` | Load filament sequence |
| **1025** | `UNLOAD_FILAMENT` | Unload filament sequence |
| **1026** | `CTRL_HOME` | Control homing axes |
| **1027** | `CTRL_MOVE` | Control manual axis movement |
| **1028** | `SET_TEMPTURE` | Set nozzle or bed temperature |
| **1029** | `CTRL_LED` | Control printer LEDs |
| **1030** | `CTRL_FAN` | Control fan speeds |
| **1031** | `CTRL_PRINT_SPEED` | Adjust printing speed |
| **1032** | `AUTO_BED_LEVELING` | Start auto bed leveling |
| **1033** | `RINGING_OPTIMIZE` | Start ringing/input shaper optimization |
| **1034** | `PID_CHECK` | Run PID tuning/check |
| **1035** | `AUTO_DIAGNOSTIC` | Run automatic diagnostics |

## File and Task Management

| Method ID | Command | Description |
| :--- | :--- | :--- |
| **1036** | `GET_HISTORY_TASK` | Get history of previous tasks |
| **1038** | `DEL_HISTORY_TASK_REPORT` | Delete history task report |
| **1044** | `GET_FILE_LIST` | Get list of files in storage |
| **1045** | `GET_FILE_THUMBNAIL` | Get thumbnail of a G-code file |
| **1046** | `GET_FILE_INFO` | Get detailed information about a file |
| **1047** | `DEL_FILE` | Delete a file from storage |
| **1048** | `GET_DISK_VALUME` | Get disk/storage volume information |

## System and Media

| Method ID | Command | Description |
| :--- | :--- | :--- |
| **1039** | `OTA_UPGRADING` | Start OTA firmware upgrade |
| **1042** | `GET_MONITOR_VIDENO_URL` | Get camera/monitor video stream URL |
| **1043** | `SET_HOSTNAME` | Set printer hostname |
| **1049** | `UPDATE_TOKEN` | Update access token |
| **1050** | `GET_TOKEN` | Get current access token |
| **1051** | `EXPORT_TIMELAPSE_VIDEO` | Export generated timelapse video |
| **1053** | `SET_TOKEN_SWITCH` | Set token authentication switch |
| **1054** | `CTRL_LIVE_STREAM` | Control live stream settings |
| **1055** | `SET_MONO_FILAMENT_INFO` | Set single filament information |
| **1056** | `EXTRUDER_FILAMENT` | Extrude/Retract filament |

## Canvas (Multicolor System)

| Method ID | Command | Description |
| :--- | :--- | :--- |
| **2001** | `CANVAS_LOAD_FILAMENT` | Load filament via Canvas |
| **2002** | `CANVAS_UNLOAD_FILAMENT` | Unload filament via Canvas |
| **2003** | `CANVAS_EDIT_FILAMENT_INFO` | Edit filament metadata in Canvas |
| **2004** | `CANVAS_AUTO_IN_FILAMENT` | Automatic filament input for Canvas |
| **2005** | `CANVAS_GET_CHANNEL_INFO` | Get status/info for Canvas channels |

## Connectivity

| Method ID | Command | Description |
| :--- | :--- | :--- |
| **6000** | `POST_INFO` | Post general information |
| **7000** | `SEARCH_BRODCAST` | Search/Discovery via broadcast |

---

## Technical Notes
- **Protocol:** SDCP over MQTT (Port 1883)
- **JSON Structure:** Requires `method` ID and optional `params`.
- **Source Reference:** `elegoo/common/method.h` (from official CentauriCarbon2 repository).

const { app, BrowserWindow, ipcMain, dialog } = require('electron');
const path = require('path');
const mqtt = require('mqtt');
const fs = require('fs');
const http = require('http');
const crypto = require('crypto');

const config = {
    server: "192.168.1.36",
    serialNo3d: "F0125XQ8BMUGJBC",
    clientId: "elegoo",
    key: "LLcnjA"
};

let mainWindow;

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1100, height: 900,
        backgroundColor: '#0f111a',
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            contextIsolation: true
        }
    });
    mainWindow.loadFile('index.html');
}

app.whenReady().then(() => {
    createWindow();
    const client = mqtt.connect(`mqtt://${config.server}:1883`, {
        username: config.clientId, password: config.key,
        clientId: 'desktop_' + Math.random().toString(16).substr(2, 4)
    });

    function sendSafeCommand(method, params = {}) {
        const regPayload = JSON.stringify({ "request_id": "php", "client_id": "php" });
        const cmdPayload = JSON.stringify({
            "id": Math.floor(Math.random() * 1000000),
            "method": method,
            "params": params
        });
        const baseTopic = `elegoo/${config.serialNo3d}`;
        client.publish(`${baseTopic}/api_register`, regPayload);
        setTimeout(() => {
            client.publish(`${baseTopic}/php/api_request`, cmdPayload);
            console.log(`MQTT SEND [${method}]: ${cmdPayload}`);
        }, 1000);
    }

    client.on('connect', () => {
        client.subscribe(`elegoo/${config.serialNo3d}/#`);
        sendSafeCommand(1002); // Initial status
    });

    client.on('message', (topic, message) => {
        if (mainWindow) mainWindow.webContents.send('mqtt-data', { topic, payload: message.toString() });
    });

    ipcMain.on('set-temp', (event, { type, value }) => {
        const params = {}; params[type] = parseInt(value);
        sendSafeCommand(1028, params);
    });

    ipcMain.on('request-files', () => {
        sendSafeCommand(1044, { "storage_media": "local", "offset": 0, "limit": 100 });
    });

    ipcMain.on('start-print', (event, { filename, slot }) => {
        sendSafeCommand(1020, {
            "filename": filename,
            "storage_media": "local",
            "config": {
                "bedlevel_force": false, "delay_video": false, "print_layout": "A", "printer_check": false,
                "slot_map": [{ "canvas_id": 0, "t": 0, "tray_id": parseInt(slot) }]
            }
        });
    });

    ipcMain.on('pause-print', () => {
        sendSafeCommand(1021);
    });

    ipcMain.on('resume-print', () => {
        sendSafeCommand(1023);
    });

    ipcMain.on('cancel-print', () => {
        sendSafeCommand(1022);
    });

    ipcMain.handle('select-file', async () => {
        const { canceled, filePaths } = await dialog.showOpenDialog({
            properties: ['openFile'],
            filters: [{ name: 'GCode', extensions: ['gcode'] }]
        });
        return canceled ? null : filePaths[0];
    });

    ipcMain.on('upload-file', (event, filePath) => {
        const filename = path.basename(filePath);
        const url = `http://${config.server}:80/upload`;

        console.log(`Uploading ${filename} to ${url}...`);
        const fileBuffer = fs.readFileSync(filePath);
        const stats = fs.statSync(filePath);
        const md5Hash = crypto.createHash('md5').update(fileBuffer).digest('hex');

        const request = http.request(url, {
            method: 'PUT',
            headers: { 
                'User-Agent': 'ElegooLink/0.0.1',
                'Accept': 'application/json',
                'Content-Type': 'application/octet-stream',
                'Content-Length': stats.size,
                'Content-Range': `bytes 0-${stats.size - 1}/${stats.size}`,
                'X-File-Name': filename,
                'X-File-MD5': md5Hash,
                'X-Token': config.key
            }
        }, (res) => {
            let data = '';
            res.on('data', (chunk) => data += chunk);
            res.on('end', () => {
                if (res.statusCode === 200) {
                    console.log('Upload successful:', data);
                    mainWindow.webContents.send('upload-success', filename);
                } else {
                    console.error(`Upload failed with status ${res.statusCode}:`, data);
                    mainWindow.webContents.send('upload-error', `Server responded with ${res.statusCode}`);
                }
            });
        });

        request.on('error', (err) => {
            console.error('Upload error:', err);
            mainWindow.webContents.send('upload-error', err.message);
        });

        request.write(fileBuffer);
        request.end();
    });
});
app.on('window-all-closed', () => { if (process.platform !== 'darwin') app.quit(); });


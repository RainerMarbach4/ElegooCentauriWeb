const { contextBridge, ipcRenderer } = require('electron');
contextBridge.exposeInMainWorld('electronAPI', {
    onMqttData: (callback) => ipcRenderer.on('mqtt-data', (_event, value) => callback(value)),
    setTemperature: (type, value) => ipcRenderer.send('set-temp', { type, value }),
    requestFiles: () => ipcRenderer.send('request-files'),
    startPrint: (filename, slot) => ipcRenderer.send('start-print', { filename, slot }),
    pausePrint: () => ipcRenderer.send('pause-print'),
    resumePrint: () => ipcRenderer.send('resume-print'),
    cancelPrint: () => ipcRenderer.send('cancel-print'),
    selectFile: () => ipcRenderer.invoke('select-file'),
    uploadFile: (path) => ipcRenderer.send('upload-file', path),
    onUploadSuccess: (callback) => ipcRenderer.on('upload-success', (_event, value) => callback(value)),
    onUploadError: (callback) => ipcRenderer.on('upload-error', (_event, value) => callback(value))
});

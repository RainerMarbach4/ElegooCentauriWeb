<?php
/**
 * file_manager.php - Optimized Printer Web Interface
 */

require_once '../config_elegoo.php';
require_once 'printer_api.php';

$config = [
    'server' => $server, 'port' => $port, 'clientId' => $clientId, 'key' => $key, 'serialNo3d' => $serialNo3d
];

// --- AJAX HANDLER ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $statusFile = __DIR__ . '/current_status.json';
    $cache = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : ['result' => [], 'files' => []];

    try {
        if ($_GET['action'] === 'list') {
            // Trigger a refresh in background, but return cache immediately
            $printer = new ElegooPrinter($config);
            $printer->connect();
            $printer->getFileList();
            $printer->disconnect();
            echo json_encode(['result' => ['files' => $cache['files'] ?? []]]);
        } 
        elseif ($_GET['action'] === 'status') {
            echo json_encode(['result' => $cache['result'] ?? [], 'last_update' => $cache['last_update'] ?? 0]);
        }
        elseif ($_GET['action'] === 'delete' && isset($_POST['files'])) {
            $printer = new ElegooPrinter($config);
            $printer->connect();
            foreach ($_POST['files'] as $file) { $printer->deleteFile($file); }
            $printer->disconnect();
            echo json_encode(['status' => 'queued']);
        }
        elseif ($_GET['action'] === 'print' && isset($_POST['filename'])) {
            $printer = new ElegooPrinter($config);
            $printer->connect();
            $printer->startPrint($_POST['filename'], $_POST['slot'] ?? 1);
            $printer->disconnect();
            echo json_encode(['status' => 'queued']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elegoo CC2 - Dashboard</title>
    <style>
        body { font-family: -apple-system, system-ui, sans-serif; background: #f0f2f5; color: #1c1e21; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .dashboard { display: flex; flex-wrap: wrap; gap: 15px; }
        .stat-box { flex: 1; min-width: 180px; background: #fafafa; padding: 15px; border-radius: 10px; border-left: 5px solid #007bff; }
        .stat-label { font-size: 12px; color: #65676b; text-transform: uppercase; font-weight: bold; }
        .stat-value { font-size: 20px; font-weight: 700; margin-top: 5px; }
        .progress-container { width: 100%; background: #e4e6eb; border-radius: 10px; height: 12px; margin: 15px 0; overflow: hidden; }
        .progress-bar { background: #28a745; height: 100%; width: 0%; transition: width 0.5s; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ebedf0; }
        th { background: #f8f9fa; color: #65676b; font-size: 13px; }
        button { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-refresh { background: #007bff; color: white; }
        .btn-print { background: #28a745; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        button:hover { opacity: 0.85; }
        button:disabled { background: #ccd0d5; cursor: not-allowed; }
        #loading-spinner { display: none; color: #007bff; font-weight: bold; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2 style="margin-top:0">Drucker Status - <?php echo $serialNo3d; ?></h2>
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1; min-width: 300px;">
                <div class="dashboard">
                    <div class="stat-box" style="border-left-color: #007bff;"><div class="stat-label">Status</div><div class="stat-value" id="disp-status">Lädt...</div></div>
                    <div class="stat-box" style="border-left-color: #dc3545;"><div class="stat-label">Düse</div><div class="stat-value" id="disp-nozzle">-- / -- °C</div></div>
                    <div class="stat-box" style="border-left-color: #fb8c00;"><div class="stat-label">Heizbett</div><div class="stat-value" id="disp-bed">-- / -- °C</div></div>
                    <div class="stat-box" style="border-left-color: #28a745;"><div class="stat-label">Fortschritt</div><div class="stat-value" id="disp-percent">0 %</div></div>
                </div>
                <div class="progress-container"><div class="progress-bar" id="progress-bar"></div></div>
                <div id="disp-job" style="font-size: 14px; color: #65676b;">Bereit</div>
            </div>
            <div style="flex: 1; min-width: 300px; background: #000; border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; min-height: 200px;">
                <img id="camera-stream" src="http://<?php echo $server; ?>:8080/?action=stream" alt="Live Kamera" style="max-width: 100%; height: auto;" onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'color:white\'>Kamera nicht erreichbar</span>'">
            </div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px">
            <h3 style="margin:0">Dateiverwaltung</h3>
            <div>
                <button class="btn-refresh" onclick="loadFiles()">Liste laden</button>
                <button id="deleteBtn" class="btn-delete" onclick="deleteSelected()" disabled>Löschen</button>
                <span id="loading-spinner">Warte...</span>
            </div>
        </div>
        <table>
            <thead><tr><th width="30"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th><th>Dateiname</th><th>Farbe (Slot)</th><th width="100">Aktion</th></tr></thead>
            <tbody id="fileList"><tr><td colspan="4" align="center">Keine Daten geladen.</td></tr></tbody>
        </table>
    </div>
</div>

<script>
    let isPrinting = false;

    async function updateStatus() {
        try {
            const res = await fetch('?action=status');
            const data = await res.json();
            if (data.result) {
                const r = data.result;
                const ext = r.extruder || {};
                const bed = r.heater_bed || {};
                document.getElementById('disp-nozzle').textContent = `${ext.temperature || 0} / ${ext.target || 0} °C`;
                document.getElementById('disp-bed').textContent = `${bed.temperature || 0} / ${bed.target || 0} °C`;
                
                if (r.machine_status) {
                    const percent = r.machine_status.progress || 0;
                    document.getElementById('disp-percent').textContent = percent + ' %';
                    document.getElementById('progress-bar').style.width = percent + '%';
                    const code = r.machine_status.status;
                    const states = {1: "Bereit", 2: "Druckt", 3: "Pause", 4: "Fehler"};
                    document.getElementById('disp-status').textContent = states[code] || "Unbekannt";
                    if (isPrinting && code === 1 && percent >= 99) {
                        alert("Druck beendet!");
                        isPrinting = false;
                        loadFiles();
                    }
                    if (code === 2) isPrinting = true;
                }
                if (r.print_status) document.getElementById('disp-job').textContent = r.print_status.filename || "Bereit";
            } else if (data.error) {
                document.getElementById('disp-status').textContent = "Fehler";
            }
        } catch (e) { document.getElementById('disp-status').textContent = "Offline"; }
    }

    async function loadFiles() {
        showLoading(true);
        try {
            const res = await fetch('?action=list');
            const data = await res.json();
            const files = (data.result && data.result.files) ? data.result.files : [];
            const tbody = document.getElementById('fileList');
            tbody.innerHTML = '';
            if (files.length === 0) tbody.innerHTML = '<tr><td colspan="4" align="center">Keine Dateien.</td></tr>';
            else {
                files.forEach(f => {
                    const name = typeof f === 'string' ? f : (f.name || f.filename);
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><input type="checkbox" class="file-check" value="${name}" onclick="updateDeleteButton()"></td>
                        <td><strong>${name}</strong></td>
                        <td><select class="slot-select" id="s_${name.replace(/\W/g,'_')}"><option value="1">Slot 1</option><option value="2">Slot 2</option><option value="3">Slot 3</option><option value="4">Slot 4</option></select></td>
                        <td><button class="btn-print" onclick="startPrint('${name}')">Druck</button></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        } catch (e) { console.error(e); }
        showLoading(false);
    }

    async function startPrint(name) {
        const slot = document.getElementById(`s_${name.replace(/\W/g,'_')}`).value;
        if (!confirm(`${name} drucken?`)) return;
        showLoading(true);
        const fd = new FormData(); fd.append('filename', name); fd.append('slot', slot);
        const res = await fetch('?action=print', {method: 'POST', body: fd});
        const data = await res.json();
        alert(data.error ? "Fehler: " + data.error : "Druck gestartet!");
        showLoading(false);
    }

    async function deleteSelected() {
        const checks = document.querySelectorAll('.file-check:checked');
        if (!confirm(`${checks.length} Dateien löschen?`)) return;
        showLoading(true);
        const fd = new FormData(); checks.forEach(c => fd.append('files[]', c.value));
        await fetch('?action=delete', {method: 'POST', body: fd});
        loadFiles();
        showLoading(false);
    }

    function showLoading(s) { document.getElementById('loading-spinner').style.display = s ? 'inline' : 'none'; }
    function toggleSelectAll() { 
        const m = document.getElementById('selectAll').checked;
        document.querySelectorAll('.file-check').forEach(c => c.checked = m);
        updateDeleteButton();
    }
    function updateDeleteButton() { document.getElementById('deleteBtn').disabled = document.querySelectorAll('.file-check:checked').length === 0; }

    setInterval(updateStatus, 5000);
    updateStatus();
    loadFiles();
</script>
</body>
</html>

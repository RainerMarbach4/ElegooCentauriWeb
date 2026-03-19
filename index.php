<?php
// Prevent directory listing by loading this file by default.
// Display the content of README.md as HTML.
header("Content-Type: text/html; charset=utf-8");
?>
<!DOCTYPE html>
<html>
<head>
    <title>ElegooCentauriWeb</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 2em; max-width: 800px; margin: auto; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <?php readfile("README.md"); ?>
</body>
</html>

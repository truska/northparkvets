<?php
$data = json_decode(file_get_contents("php://input"), true);
$version = $data['version'] ?? 'Unknown';
// Save it to session, DB, or display directly
echo "Bootstrap version received: " . htmlspecialchars($version);

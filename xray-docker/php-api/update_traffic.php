<?php
$USERS_FILE = '/etc/xray/users.json';
$LOG_FILE   = '/var/log/xray/access.log';
$API_URL    = getenv('MAIN_API_URL') ?: 'http://main-server/api/traffic';
$API_KEY    = getenv('MAIN_API_KEY') ?: 'changeme';

$users = json_decode(file_get_contents($USERS_FILE), true) ?: [];
$traffic = [];

$handle = fopen($LOG_FILE, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (preg_match('/user-id=(\w+).*upload=(\d+).*download=(\d+)/', $line, $m)) {
            $id = $m[1];
            $traffic[$id] = ($traffic[$id] ?? 0) + $m[2] + $m[3];
        }
    }
    fclose($handle);
}

$send = [];
foreach ($users as $u) {
    $id = $u['id'];
    if (isset($traffic[$id])) {
        $send[] = [
            'vpn_user_id' => $id,
            'traffic_used' => $traffic[$id]
        ];
    }
}

if (!empty($send)) {
    $ch = curl_init($API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $API_KEY
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($send));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    curl_close($ch);
    echo "Traffic sent: " . count($send) . " users\n";
}

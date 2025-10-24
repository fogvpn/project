<?php
header('Content-Type: application/json; charset=utf-8');

$headers = function_exists('getallheaders') ? getallheaders() : [];
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$expected = 'Bearer ' . ($_ENV['API_KEY'] ?? '');

if ($auth !== $expected) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid API key']); exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: [];

$vpnUserId = $input['vpn_user_id'] ?? null;
if (!$vpnUserId) {
    http_response_code(400);
    echo json_encode(['error' => 'vpn_user_id is required']); exit;
}

$usersFile = '/etc/xray/users.json';
$list = json_decode(@file_get_contents($usersFile), true) ?: [];

$email = null;
foreach ($list as $u) {
    if (!empty($u['id']) && $u['id'] === $vpnUserId) {
        $email = $u['email'] ?? null;
        break;
    }
}
if (!$email) {
    http_response_code(404);
    echo json_encode(['error' => 'email not found for this vpn_user_id']); exit;
}

$serverInternal = ($_ENV['XRAY_SERVER_INTERNAL'] ?? 'xray') . ':10085';
$cmd = sprintf(
    '/usr/local/bin/xraystats -email %s -server %s 2>&1',
    escapeshellarg($email),
    escapeshellarg($serverInternal)
);
exec($cmd, $out, $ec);
if ($ec !== 0) {
    http_response_code(502);
    echo json_encode(['error' => 'xraystats failed', 'detail' => implode("\n", $out)]);
    exit;
}
$data = json_decode(implode("\n", $out), true) ?: [];

echo json_encode([
    'email' => $email,
    'vpn_user_id' => $vpnUserId,
    'up_bytes' => (int)($data['UpBytes'] ?? $data['up_bytes'] ?? 0),
    'down_bytes' => (int)($data['DownBytes'] ?? $data['down_bytes'] ?? 0),
    'total_bytes' => (int)($data['TotalBytes'] ?? $data['total_bytes'] ?? 0)
]);

<?php
header('Content-Type: application/json; charset=utf-8');

$headers = function_exists('getallheaders') ? getallheaders() : [];
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$expected = 'Bearer ' . ($_ENV['API_KEY'] ?? '');

if ($auth !== $expected) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?: [];

if (empty($input['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit;
}

$email = $input['email'];

function uuidv4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$id = uuidv4();
$shortId = $_ENV['XRAY_SHORT_ID'];

$server = $_ENV['XRAY_SERVER_PUBLIC'] ?? '127.0.0.1';
$pbk = $_ENV['XRAY_PBK'] ?? '';
$sid = $shortId;

$vlessUrl = sprintf(
    'vless://%s@%s:443?encryption=none&security=reality&flow=xtls-rprx-vision&fp=chrome&type=tcp&sni=%s&pbk=%s&sid=%s#%s',
    $id,
    $server,
    rawurlencode($_ENV['XRAY_SNI']),
    rawurlencode($pbk),
    rawurlencode($sid),
    rawurlencode('🇳🇱FOG-VLESS')
);

$usersFile = '/etc/xray/users.json';
if (!file_exists(dirname($usersFile))) {
    @mkdir(dirname($usersFile), 0755, true);
}
if (!file_exists($usersFile)) {
    @file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
$users = json_decode(@file_get_contents($usersFile), true) ?: [];
$users[] = [
    'id' => $id,
    'email' => $email,
    'created' => date('c')
];
@file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

$xrayctl = '/usr/local/bin/xrayctl';
$serverInternal = ($_ENV['XRAY_SERVER_INTERNAL'] ?? 'xray') . ':10085';

$cmd = escapeshellcmd($xrayctl) . ' -tag vless -id ' . escapeshellarg($id) .
       ' -email ' . escapeshellarg($email) . ' -server ' . escapeshellarg($serverInternal) . ' 2>&1';

exec($cmd, $out, $ec);
$outText = implode("\n", $out);

if ($ec !== 0) {
    http_response_code(500);
    echo json_encode([
        'error' => 'xray api failed',
        'detail' => $outText,
        'cmd' => $cmd
    ]);
    exit;
}

http_response_code(201);
echo json_encode([
    'id' => $id,
    'shortId' => $shortId,
    'vless_url' => $vlessUrl
]);

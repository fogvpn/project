<?php
function vpn_fetch_traffic_total(?string $vpnUserId, bool $reset = false): ?int {
    if (!$vpnUserId) return null;

    $vpnApiBase = $_ENV['VPN_API_URL'] ?? null;
    $vpnApiKey  = $_ENV['VPN_API_KEY'] ?? null;
    if (!$vpnApiBase || !$vpnApiKey) return null;

    $url = rtrim($vpnApiBase, '/') . '/update_traffic.php';
    $payload = ['vpn_user_id' => $vpnUserId];
    if ($reset) $payload['reset'] = true;

    try {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer '.$vpnApiKey
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 8,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$resp) return null;
        $data = json_decode($resp, true) ?: [];

        return isset($data['total_bytes']) ? (int)$data['total_bytes'] : null;
    } catch (\Throwable $e) {
        return null;
    }
}

<?php
Flight::route('GET /api/me', function() use ($pdo) {
    $token = $_COOKIE['session_token'] ?? null;
    if (!$token) { Flight::json(['authenticated' => false]); return; }

    $stmt = $pdo->prepare("
        SELECT 
            u.id AS user_id,
            u.uuid AS uuid,
            u.status,
            u.traffic_used,
            u.cycle_start_at,
            u.cycle_end_at,
            u.config_link,
            p.id as plan_id,
            p.name as plan_name,
            p.traffic_limit,
            p.duration,
            c.id as config_id,
            c.vpn_user_id as vpn_user_id
        FROM users u
        JOIN configs c ON c.user_id = u.id
        JOIN sessions s ON s.user_id = u.id
        JOIN plans p ON p.id = u.plan_id
        WHERE s.token = ? AND s.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        setcookie('session_token', '', time() - 3600, '/', '', true, true);
        Flight::json(['authenticated' => false]); return;
    }

    $freshTotal = null;
    try {
        $vpnApiBase = $_ENV['VPN_API_URL'] ?? null;
        $vpnApiKey  = $_ENV['VPN_API_KEY'] ?? null;
        if ($vpnApiBase && $vpnApiKey && !empty($user['vpn_user_id'])) {
            $url = rtrim($vpnApiBase, '/') . '/update_traffic.php';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer '.$vpnApiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['vpn_user_id' => $user['vpn_user_id']]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200 && $resp) {
                $payload = json_decode($resp, true) ?: [];
                $freshTotal = (int)($payload['total_bytes'] ?? 0);
            }
        }
    } catch (\Throwable $e) {
    }

    $now = new DateTimeImmutable('now');
    $cycleEnd = !empty($user['cycle_end_at']) ? new DateTimeImmutable($user['cycle_end_at']) : null;
    $didReset = false;

    if ($cycleEnd && $now >= $cycleEnd) {
        $stmt = $pdo->prepare("
            UPDATE users
            SET traffic_used = 0,
                cycle_start_at = NOW(),
                cycle_end_at = NOW() + INTERVAL '1 month'
            WHERE id = ?
        ");
        $stmt->execute([(int)$user['user_id']]);
        $didReset = true;

        try {
            if (!empty($user['vpn_user_id']) && $vpnApiBase && $vpnApiKey) {
                $url = rtrim($vpnApiBase, '/') . '/update_traffic.php';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$vpnApiKey
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['vpn_user_id' => $user['vpn_user_id'], 'reset' => true]));
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_exec($ch);
                curl_close($ch);
            }
        } catch (\Throwable $e) {}
    }

    if ($freshTotal !== null && !$didReset) {
        $stmt = $pdo->prepare("UPDATE users SET traffic_used = ? WHERE id = ?");
        $stmt->execute([$freshTotal, (int)$user['user_id']]);
        $user['traffic_used'] = $freshTotal;
    } elseif ($didReset) {
        $user['traffic_used'] = 0;
    }

    $limit = (int)$user['traffic_limit'];
    $used = (int)$user['traffic_used'];
    $remaining = $limit === -1 ? -1 : max($limit - $used, 0);

    if ($limit !== -1 && $remaining === 0 && $user['status'] === 'active') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$user['user_id']]);
        $user['status'] = 'inactive';
    }

    $subscriptionUrl = $_ENV['BASE_URL'] . "sub/" . $user['config_link'];

    Flight::json([
        'uuid'           => $user['uuid'],
        'subscription'   => $subscriptionUrl,
        'status'         => $user['status'],
        'traffic_used'   => (int)$user['traffic_used'],
        'traffic_limit'  => $limit,
        'traffic_left'   => $remaining,
        'plan_id'        => (int)$user['plan_id'],
        'plan_name'      => $user['plan_name'],
        'cycle_start_at' => $user['cycle_start_at'],
        'cycle_end_at'   => $user['cycle_end_at'],
        'duration'       => $user['duration']
    ]);
});

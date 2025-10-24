<?php
Flight::route('GET /api/me', function() use ($pdo) {
    $token = $_COOKIE['session_token'] ?? null;
    if (!$token) {
        Flight::json(['authenticated' => false]);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT 
            u.id               AS user_id,
            u.uuid             AS uuid,
            u.status           AS status,
            u.traffic_used     AS traffic_used,
            u.cycle_start_at   AS cycle_start_at,
            u.cycle_end_at     AS cycle_end_at,
            u.config_link      AS config_link,
            p.id               AS plan_id,
            p.name             AS plan_name,
            p.traffic_limit    AS traffic_limit,
            p.duration         AS duration,
            c.id               AS config_id,
            c.vpn_user_id      AS vpn_user_id
        FROM users u
        JOIN configs  c ON c.user_id = u.id
        JOIN sessions s ON s.user_id = u.id
        JOIN plans    p ON p.id     = u.plan_id
        WHERE s.token = ? AND s.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        setcookie('session_token', '', time() - 3600, '/', '', true, true);
        Flight::json(['authenticated' => false]);
        return;
    }

    require_once __DIR__ . '/../functions/fetch_traffic.php';

    if (!empty($user['vpn_user_id'])) {
        $freshTotal = vpn_fetch_traffic_total($user['vpn_user_id']);
        if ($freshTotal !== null) {
            try {
                $upd = $pdo->prepare("UPDATE users SET traffic_used = ? WHERE id = ?");
                $upd->execute([(int)$freshTotal, (int)$user['user_id']]);
                $user['traffic_used'] = (int)$freshTotal;
            } catch (\Throwable $e) {
            }
        }
    }

    $limit = (int)$user['traffic_limit'];
    $used  = (int)$user['traffic_used'];
    $remaining = $limit === -1 ? -1 : max($limit - $used, 0);

    $subscriptionUrl = rtrim((string)($_ENV['BASE_URL'] ?? ''), '/') . "/sub/" . $user['config_link'];

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

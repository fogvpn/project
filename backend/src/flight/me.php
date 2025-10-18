<?php
Flight::route('GET /api/me', function() use ($pdo) {
    $token = $_COOKIE['session_token'] ?? null;
    if (!$token) Flight::halt(401);

    $stmt = $pdo->prepare("
        SELECT 
            u.id AS user_id,
            u.status,
            u.traffic_used,
            u.cycle_start_at,
            u.cycle_end_at,
            u.config_link,
            p.id as plan_id,
            p.name as plan_name,
            p.traffic_limit,
            p.duration,
            c.id as config_id
        FROM users u
        JOIN configs c ON c.user_id = u.id
        JOIN sessions s ON s.user_id = u.id
        JOIN plans p ON p.id = u.plan_id
        WHERE s.token = ? AND s.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) Flight::halt(401);

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
        'subscription'   => $subscriptionUrl,
        'status'         => $user['status'],
        'traffic_used'   => $used,
        'traffic_limit'  => $limit,
        'traffic_left'   => $remaining,
        'plan_id'        => (int)$user['plan_id'],
        'plan_name'      => $user['plan_name'],
        'cycle_start_at' => $user['cycle_start_at'],
        'cycle_end_at'   => $user['cycle_end_at'],
        'duration'       => $user['duration']
    ]);
});

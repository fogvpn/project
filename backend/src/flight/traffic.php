<?php
Flight::route('POST /api/traffic', function() use ($pdo) {
    $headers = getallheaders();
    $auth = $headers['Authorization'] ?? ($headers['authorization'] ?? null);
    if (!$auth || $auth !== 'Bearer ' . ($_ENV['VPN_API_KEY'] ?? 'changeme')) {
        Flight::halt(401, 'Unauthorized');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) Flight::halt(400, 'No data');

    foreach ($data as $u) {
        $vpnId = $u['vpn_user_id'] ?? null;
        $used = (int)($u['traffic_used'] ?? 0);
        if (!$vpnId) continue;

        $stmt = $pdo->prepare("
            UPDATE users u
            SET traffic_used = GREATEST(u.traffic_used, ?)
            FROM configs c
            WHERE c.user_id = u.id AND c.vpn_user_id = ?
        ");
        $stmt->execute([$used, $vpnId]);

        $stmt2 = $pdo->prepare("
            UPDATE users u
            SET status = 'inactive'
            FROM configs c
            JOIN plans p ON p.id = u.plan_id
            WHERE c.user_id = u.id
              AND c.vpn_user_id = ?
              AND p.traffic_limit != -1
              AND u.traffic_used >= p.traffic_limit
              AND u.status = 'active'
        ");
        $stmt2->execute([$vpnId]);
    }

    Flight::json(['success' => true]);
});

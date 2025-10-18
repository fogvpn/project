<?php
Flight::route('POST /api/login', function() use ($pdo) {
    $data = Flight::request()->data->getData();
    $uuid = $data['uuid'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (!$uuid || !preg_match('/^[A-Za-z0-9!@#$%^&*()\-_=+\[\]{};:,.<>?]{43}$/', $uuid)) {
        Flight::halt(400, "Invalid Account ID");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM auth_logs WHERE ip = ? AND action = 'login' AND created_at > NOW() - INTERVAL '1 minute'");
    $stmt->execute([$ip]);
    $attempts_min = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM auth_logs WHERE ip = ? AND action = 'login' AND created_at > NOW() - INTERVAL '1 hour'");
    $stmt->execute([$ip]);
    $attempts_hour = (int)$stmt->fetchColumn();

    if ($attempts_min >= 5 || $attempts_hour >= 50) {
        Flight::halt(429, "Too many requests. Try again later");
    }

    $stmt = $pdo->prepare("SELECT id, config_link FROM users WHERE uuid = ? LIMIT 1");
    $stmt->execute([$uuid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $stmt = $pdo->prepare("INSERT INTO auth_logs (user_id, ip, action, success) VALUES (?, ?, 'login', false)");
        $stmt->execute([null, $ip]);
        Flight::halt(401, "Unauthorized");
    }

    $userId = $user['id'];
    $configLink = $user['config_link'];

    $sessionToken = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, NOW() + INTERVAL '30 days')");
    $stmt->execute([$userId, $sessionToken]);

    setcookie("session_token", $sessionToken, [
        'expires' => time() + 60*60*24*30,
        'path' => '/',
        'secure' => false, //true
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    $stmt = $pdo->prepare("INSERT INTO auth_logs (user_id, ip, action, success) VALUES (?, ?, 'login', true)");
    $stmt->execute([$userId, $ip]);

    Flight::json([
        'subscription' => $_ENV['BASE_URL'] . "sub/" . $configLink
    ]);
});
?>

<?php
Flight::route('POST /api/register', function() use ($pdo) {
    $data = Flight::request()->data->getData();
    $uuid = $data['uuid'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $captcha = $data['captcha'] ?? null;

    if (!$uuid || !preg_match('/^[A-Za-z0-9!@#$%^&*()\-_=+\[\]{};:,.<>?]{43}$/', $uuid)) {
        Flight::halt(400, "Invalid Account ID");
    }

    // require_once __DIR__ . '/../functions/hcaptcha.php';
    
    // if (!$captcha) {
    //     http_response_code(400);
    //     echo "Please complete the hCaptcha";
    //     exit;
    // }

    // if (!verify_hcaptcha($captcha)) {
    //     http_response_code(403);
    //     echo "hCaptcha verification failed";
    //     exit;
    // }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM auth_logs WHERE ip = ? AND action = 'register' AND created_at > NOW() - INTERVAL '1 minute'");
    $stmt->execute([$ip]);
    $attempts_min = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM auth_logs WHERE ip = ? AND action = 'register' AND created_at > NOW() - INTERVAL '1 hour'");
    $stmt->execute([$ip]);
    $attempts_hour = (int)$stmt->fetchColumn();

    if ($attempts_min >= 3 || $attempts_hour >= 10) {
        Flight::halt(429, "Too many requests");
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE uuid = ?");
    $stmt->execute([$uuid]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO auth_logs (user_id, ip, action, success) VALUES (?, ?, 'register', false)");
        $stmt->execute([null, $ip]);
        Flight::halt(409, "User already exists");
    }

    try {
        $pdo->beginTransaction();
    
        $vpnApiBase = $_ENV['VPN_API_URL'] ?? null;
        $vpnApiUrl = $vpnApiBase ? rtrim($vpnApiBase, '/') . '/create_user.php' : null;
        $vpnApiKey = $_ENV['VPN_API_KEY'] ?? null;
    
        $ch = curl_init($vpnApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$vpnApiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => 'user-' . bin2hex(random_bytes(6))
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($httpcode !== 200 && $httpcode !== 201) {
            $pdo->rollBack();
            Flight::halt(502, "Failed to create VPN profile. HTTP: $httpcode, CURL: $curlError, RESPONSE: $response");
        }
        curl_close($ch);
    
        if ($httpcode !== 200 && $httpcode !== 201) {
            $pdo->rollBack();
            Flight::halt(502, "Failed to create VPN profile");
        }
    
        $data = json_decode($response, true);
        if (!$data || empty($data['id'])) {
            $pdo->rollBack();
            Flight::halt(502, "Invalid VPN response");
        }
    
        $vpnUserId = $data['id'];
        $shortId   = $data['shortId'] ?? null;
        $vlessUrl  = $data['vless_url'] ?? null;
    
        $configLink = $vpnUserId;
    
        $stmt = $pdo->prepare("
            INSERT INTO users (uuid, plan_id, config_link) 
            VALUES (?, (SELECT id FROM plans WHERE name='free'), ?) 
            RETURNING id
        ");
        $stmt->execute([$uuid, $configLink]);
        $userId = $stmt->fetchColumn();
    
        $stmt = $pdo->prepare("
            INSERT INTO configs (user_id, vpn_user_id, short_id, vless_url, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $vpnUserId, $shortId, $vlessUrl]);
    
        $sessionToken = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("
            INSERT INTO sessions (user_id, token, expires_at)
            VALUES (?, ?, NOW() + INTERVAL '30 days')
        ");
        $stmt->execute([$userId, $sessionToken]);
    
        setcookie("session_token", $sessionToken, [
            'expires' => time() + 60*60*24*30,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    
        $stmt = $pdo->prepare("
            INSERT INTO auth_logs (user_id, ip, action, success)
            VALUES (?, ?, 'register', true)
        ");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt->execute([$userId, $ip]);
    
        $pdo->commit();
    
        Flight::json([
            'subscription' => $_ENV['BASE_URL'] . "sub/" . $configLink
        ]);
    
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        Flight::halt(500, "Registration failed: " . $e->getMessage());
    }
});

?>
<?php
Flight::route('GET /api/payments', function() use ($pdo) {
    $sessionToken = $_COOKIE['session_token'] ?? null;
    if (!$sessionToken) {
        Flight::halt(401, "Unauthorized");
    }

    $stmt = $pdo->prepare("SELECT user_id FROM sessions WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$sessionToken]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        Flight::halt(401, "Invalid session");
    }

    $userId = $session['user_id'];

    $stmt = $pdo->prepare("SELECT id, amount, currency, plan_id, status, created_at, paid_at 
                           FROM payments 
                           WHERE user_id = ? 
                           ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    Flight::json($payments ?: []);
});
?>

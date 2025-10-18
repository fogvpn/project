<?php
Flight::route('POST /api/payment', function() use ($pdo) {
    $token = $_COOKIE['session_token'] ?? null;
    if (!$token) {
        Flight::halt(401, "Not authenticated");
    }

    $stmt = $pdo->prepare("
        SELECT u.id, u.uuid
        FROM users u
        JOIN sessions s ON s.user_id = u.id
        WHERE s.token = ? AND s.expires_at > NOW()
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        Flight::halt(401, "Not authenticated");
    }

    $data = Flight::request()->data->getData();
    $planName = $data['plan'] ?? null;

    if (!$planName) {
        Flight::halt(400, "Plan is required");
    }

    $stmt = $pdo->prepare("SELECT id, price, currency FROM plans WHERE name = ?");
    $stmt->execute([$planName]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        Flight::halt(404, "Plan not found");
    }

    $stmt = $pdo->prepare("
        INSERT INTO payments (user_id, amount, currency, plan_id, status)
        VALUES (?, ?, ?, ?, 'pending')
        RETURNING id
    ");
    $stmt->execute([
        $user['id'],
        $plan['price'],
        $plan['currency'] ?? 'USD',
        $plan['id']
    ]);
    $paymentId = $stmt->fetchColumn();
    $paymentUrl = $_ENV['BASE_URL'] . "checkout.php?payment_id=" . $paymentId;

    Flight::json([
        'url' => $paymentUrl
    ]);
});
?>

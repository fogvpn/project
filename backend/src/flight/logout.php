<?php
Flight::route('POST /api/logout', function() use ($pdo) {
    $token = $_COOKIE['session_token'] ?? null;
    if ($token) {
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->execute([$token]);
    }

    setcookie("session_token", "", [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    Flight::json(['success' => true]);
});
?>
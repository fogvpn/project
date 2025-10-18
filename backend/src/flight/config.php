<?php
Flight::route('GET /sub/@link', function($link) use ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.vless_url
            FROM configs c
            JOIN users u ON u.id = c.user_id
            WHERE u.config_link = ?
            LIMIT 1
        ");
        $stmt->execute([$link]);
        $cfg = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cfg || empty($cfg['vless_url'])) {
            Flight::halt(404, "Config not found");
        }

        header('Content-Type: text/plain');
        echo $cfg['vless_url'];

    } catch (PDOException $e) {
        Flight::halt(500, "DB error: " . $e->getMessage());
    }
});

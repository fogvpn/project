<?php
Flight::route('POST /api/expire_subscriptions', function() use ($pdo) {
    $stmt = $pdo->prepare("
        UPDATE users u
        SET 
            plan_id = (SELECT id FROM plans WHERE name='free'),
            traffic_used = 0,
            cycle_start_at = NOW(),
            cycle_end_at = NOW() + INTERVAL '1 day',
            status = 'active'
        WHERE u.cycle_end_at < NOW()
          AND u.plan_id != (SELECT id FROM plans WHERE name='free')
    ");
    $stmt->execute();

    Flight::json(['updated' => $stmt->rowCount()]);
});

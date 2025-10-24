<?php
Flight::route('POST /cron/traffic', function() use ($pdo) {
    $key = Flight::request()->query['key'] ?? Flight::request()->data['key'] ?? null;
    if (!$key || $key !== ($_ENV['CRON_KEY'] ?? '')) {
        Flight::halt(403, 'Forbidden');
    }

    $lockStmt = $pdo->query("SELECT pg_try_advisory_lock(987654321) AS got");
    $got = (int)$lockStmt->fetch(PDO::FETCH_ASSOC)['got'];
    if (!$got) {
        Flight::halt(423, 'Cron is already running');
    }

    $batch = 500;
    $offset = 0;
    $processed = 0; $resets = 0; $updated = 0;

    require_once __DIR__ . '/../functions/fetch_traffic.php';

    while (true) {
        $stmt = $pdo->prepare("
            SELECT u.id, u.cycle_start_at, u.cycle_end_at, u.traffic_used, u.plan_id,
                   p.traffic_limit, c.vpn_user_id
            FROM users u
            JOIN plans p ON p.id = u.plan_id
            LEFT JOIN configs c ON c.user_id = u.id
            ORDER BY u.id
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', $batch, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) break;

        foreach ($rows as $u) {
            $processed++;
            $now = new DateTimeImmutable('now');
            $start = new DateTimeImmutable($u['cycle_start_at']);
            $end   = new DateTimeImmutable($u['cycle_end_at'] ?? $u['cycle_start_at']);

            $hasVpnId = !empty($u['vpn_user_id']);

            if ($now >= $end) {
                $newStart = $end;
                $newEnd = $end;
                while ($now >= $newEnd) {
                    $newStart = $newEnd;
                    $newEnd = $newEnd->modify('+1 month');
                }

                $q = $pdo->prepare("
                    UPDATE users
                    SET traffic_used = 0,
                        cycle_start_at = :start,
                        cycle_end_at   = :end
                    WHERE id = :id
                ");
                $q->execute([
                    ':start' => $newStart->format('c'),
                    ':end'   => $newEnd->format('c'),
                    ':id'    => (int)$u['id'],
                ]);
                $resets++;

                if ($hasVpnId) {
                    $total = vpn_fetch_traffic_total($u['vpn_user_id'], true);
                    if ($total !== null) {
                        $upd = $pdo->prepare("UPDATE users SET traffic_used = :used WHERE id = :id");
                        $upd->execute([':used' => (int)$total, ':id' => (int)$u['id']]);
                        $updated++;
                    }
                }

                continue;
            }

            if ($hasVpnId) {
                $total = vpn_fetch_traffic_total($u['vpn_user_id'], false);
                if ($total !== null) {
                    $upd = $pdo->prepare("UPDATE users SET traffic_used = :used WHERE id = :id");
                    $upd->execute([':used' => (int)$total, ':id' => (int)$u['id']]);
                    $updated++;
                }
            }
        }

        $offset += $batch;
    }

    $pdo->query("SELECT pg_advisory_unlock(987654321)");

    Flight::json([
        'ok' => true,
        'processed' => $processed,
        'resets' => $resets,
        'updated' => $updated
    ]);
});

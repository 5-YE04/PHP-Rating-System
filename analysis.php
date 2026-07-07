<?php
require 'config.php';
require 'helpers.php';

$overall = $pdo->query("
    SELECT COUNT(*) AS total,
           COALESCE(AVG(overall_stars), 0) AS avg_overall,
           COALESCE(AVG(staff_stars), 0) AS avg_staff,
           COALESCE(AVG(speed_stars), 0) AS avg_speed,
           COALESCE(AVG((overall_stars + staff_stars + speed_stars) / 3), 0) AS avg_combined
    FROM responses
")->fetch();

$by_device = $pdo->query("
    SELECT d.id AS device_id, d.name AS device_name,
           COUNT(r.id) AS total,
           COALESCE(AVG(r.overall_stars), 0) AS avg_overall,
           COALESCE(AVG(r.staff_stars), 0) AS avg_staff,
           COALESCE(AVG(r.speed_stars), 0) AS avg_speed,
           COALESCE(AVG((r.overall_stars + r.staff_stars + r.speed_stars) / 3), 0) AS avg_combined
    FROM devices d
    LEFT JOIN responses r ON r.device_id = d.id
    GROUP BY d.id
    ORDER BY d.id
")->fetchAll();

$recent = $pdo->query("
    SELECT r.*, d.name AS device_name
    FROM responses r
    JOIN devices d ON d.id = r.device_id
    ORDER BY r.created_at DESC LIMIT 15
")->fetchAll();

$active_devices = $pdo->query("SELECT id, name FROM devices WHERE active = 1 ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analysis — Feedback Kiosk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
    <header class="site">
        <h1>Feedback <span>Analysis</span></h1>
        <nav class="site">
            <?php foreach ($active_devices as $ad): ?>
                <a href="kiosk.php?device=<?= (int)$ad['id'] ?>"><?= e($ad['name']) ?></a>
            <?php endforeach; ?>
            <a href="devices.php">Devices</a>
        </nav>
    </header>

    <p class="eyebrow">Overall</p>
    <div class="stat-row">
        <div class="stat">
            <div class="num"><?= (int)$overall['total'] ?></div>
            <div class="label">Total responses</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($overall['avg_combined'], 2) ?></div>
            <div class="label">Combined average</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($overall['avg_overall'], 2) ?></div>
            <div class="label">Avg — Overall service</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($overall['avg_staff'], 2) ?></div>
            <div class="label">Avg — Staff friendliness</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($overall['avg_speed'], 2) ?></div>
            <div class="label">Avg — Speed of service</div>
        </div>
    </div>

    <div class="card">
        <p class="eyebrow">By device (iPad station)</p>
        <table class="analysis">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Responses</th>
                    <th>Combined avg</th>
                    <th>Overall service</th>
                    <th>Staff friendliness</th>
                    <th>Speed of service</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($by_device as $row): ?>
                    <tr>
                        <td><a href="device_detail.php?id=<?= (int)$row['device_id'] ?>"><?= e($row['device_name']) ?></a></td>
                        <td><?= (int)$row['total'] ?></td>
                        <td><?= number_format($row['avg_combined'], 2) ?></td>
                        <td><?= number_format($row['avg_overall'], 2) ?></td>
                        <td><?= number_format($row['avg_staff'], 2) ?></td>
                        <td><?= number_format($row['avg_speed'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($by_device)): ?>
                    <tr><td colspan="6" class="empty">No responses yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <p class="eyebrow">Recent responses</p>
        <table class="analysis">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Device</th>
                    <th>Overall</th>
                    <th>Staff</th>
                    <th>Speed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><?= date('M j, Y g:ia', strtotime($r['created_at'])) ?></td>
                        <td><?= e($r['device_name']) ?></td>
                        <td><?= render_stars((float)$r['overall_stars']) ?></td>
                        <td><?= render_stars((float)$r['staff_stars']) ?></td>
                        <td><?= render_stars((float)$r['speed_stars']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="5" class="empty">No responses yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

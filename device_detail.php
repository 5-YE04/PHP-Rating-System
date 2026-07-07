<?php
require 'config.php';
require 'helpers.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
$stmt->execute([$id]);
$device = $stmt->fetch();

if (!$device) {
    header('Location: devices.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total,
           COALESCE(AVG(overall_stars), 0) AS avg_overall,
           COALESCE(AVG(staff_stars), 0) AS avg_staff,
           COALESCE(AVG(speed_stars), 0) AS avg_speed,
           COALESCE(AVG((overall_stars + staff_stars + speed_stars) / 3), 0) AS avg_combined
    FROM responses WHERE device_id = ?
");
$stmt->execute([$id]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM responses WHERE device_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->execute([$id]);
$responses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($device['name']) ?> — Analysis</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
    <header class="site">
        <h1><?= e($device['name']) ?></h1>
        <nav class="site">
            <a href="devices.php">Devices</a>
            <a href="analysis.php">All stations</a>
        </nav>
    </header>

    <p class="detail-meta"><?= $device['location'] ? e($device['location']) : 'No location set' ?> · <?= $device['active'] ? 'Active' : 'Inactive' ?></p>

    <div class="stat-row">
        <div class="stat">
            <div class="num"><?= (int)$stats['total'] ?></div>
            <div class="label">Total responses</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($stats['avg_combined'], 2) ?></div>
            <div class="label">Combined average</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($stats['avg_overall'], 2) ?></div>
            <div class="label">Overall service</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($stats['avg_staff'], 2) ?></div>
            <div class="label">Staff friendliness</div>
        </div>
        <div class="stat">
            <div class="num"><?= number_format($stats['avg_speed'], 2) ?></div>
            <div class="label">Speed of service</div>
        </div>
    </div>

    <div class="card">
        <p class="eyebrow">Responses (most recent 30)</p>
        <table class="analysis">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Overall</th>
                    <th>Staff</th>
                    <th>Speed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($responses as $r): ?>
                    <tr>
                        <td><?= date('M j, Y g:ia', strtotime($r['created_at'])) ?></td>
                        <td><?= render_stars((float)$r['overall_stars']) ?></td>
                        <td><?= render_stars((float)$r['staff_stars']) ?></td>
                        <td><?= render_stars((float)$r['speed_stars']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($responses)): ?>
                    <tr><td colspan="4" class="empty">No responses yet for this station.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

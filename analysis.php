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
    SELECT device_id,
           COUNT(*) AS total,
           COALESCE(AVG(overall_stars), 0) AS avg_overall,
           COALESCE(AVG(staff_stars), 0) AS avg_staff,
           COALESCE(AVG(speed_stars), 0) AS avg_speed,
           COALESCE(AVG((overall_stars + staff_stars + speed_stars) / 3), 0) AS avg_combined
    FROM responses
    GROUP BY device_id
    ORDER BY device_id
")->fetchAll();

$recent = $pdo->query("
    SELECT * FROM responses ORDER BY created_at DESC LIMIT 15
")->fetchAll();
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
            <a href="kiosk.php?device=1">Station 1</a>
            <a href="kiosk.php?device=2">Station 2</a>
            <a href="kiosk.php?device=3">Station 3</a>
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
                    <th>Station</th>
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
                        <td>Station <?= (int)$row['device_id'] ?></td>
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
                    <th>Station</th>
                    <th>Overall</th>
                    <th>Staff</th>
                    <th>Speed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><?= date('M j, Y g:ia', strtotime($r['created_at'])) ?></td>
                        <td>Station <?= (int)$r['device_id'] ?></td>
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

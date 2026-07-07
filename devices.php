<?php
require 'config.php';
require 'helpers.php';

$error = '';

// Add a new device
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if ($name === '') {
        $error = 'Please enter a name for the device.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO devices (name, location) VALUES (?, ?)");
        $stmt->execute([$name, $location ?: null]);
        header('Location: devices.php?msg=' . urlencode("\"$name\" added."));
        exit;
    }
}

// Toggle active/inactive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE devices SET active = 1 - active WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: devices.php');
    exit;
}

$devices = $pdo->query("
    SELECT d.*, COUNT(r.id) AS response_count
    FROM devices d
    LEFT JOIN responses r ON r.device_id = d.id
    GROUP BY d.id
    ORDER BY d.id
")->fetchAll();

// Figure out the base URL so the copy-able kiosk links are correct (works for localhost or LAN IP)
$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Devices — Feedback Kiosk</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
    <header class="site">
        <h1>Manage <span>Devices</span></h1>
        <nav class="site">
            <a href="devices.php">Devices</a>
            <a href="analysis.php">Analysis</a>
        </nav>
    </header>

    <?php if (isset($_GET['msg'])): ?>
        <div class="flash"><?= e($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
        <p class="eyebrow">All stations</p>
        <table class="analysis">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Responses</th>
                    <th>Status</th>
                    <th>Kiosk link</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($devices as $d): ?>
                    <?php $link = $base_url . '/kiosk.php?device=' . (int)$d['id']; ?>
                    <tr>
                        <td><?= e($d['name']) ?></td>
                        <td><?= $d['location'] ? e($d['location']) : '—' ?></td>
                        <td><a href="device_detail.php?id=<?= (int)$d['id'] ?>"><?= (int)$d['response_count'] ?></a></td>
                        <td><?= $d['active'] ? 'Active' : 'Inactive' ?></td>
                        <td><code style="font-size:0.78rem;"><?= e($link) ?></code></td>
                        <td>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="margin:0;padding:6px 12px;font-size:0.8rem;">
                                    <?= $d['active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($devices)): ?>
                    <tr><td colspan="6" class="empty">No devices yet — add one below.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <p class="eyebrow">Add a new station</p>
        <?php if ($error): ?>
            <div class="flash" style="background:#F7E9E4;border-color:var(--terracotta);color:#8a341a;"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <label>Name</label>
            <input type="text" name="name" required placeholder="e.g. Drive-thru iPad">
            <label>Location (optional)</label>
            <input type="text" name="location" placeholder="e.g. Back entrance">
            <button type="submit">Add device</button>
        </form>
    </div>
</div>
</body>
</html>

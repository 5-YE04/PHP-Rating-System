<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kiosk.php');
    exit;
}

$device_id = (int)($_POST['device_id'] ?? 0);
$overall = (int)($_POST['overall_stars'] ?? 0);
$staff = (int)($_POST['staff_stars'] ?? 0);
$speed = (int)($_POST['speed_stars'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM devices WHERE id = ? AND active = 1");
$stmt->execute([$device_id]);
$valid_device = (bool)$stmt->fetch();

$valid_stars = function ($v) { return $v >= 1 && $v <= 5; };

if (!$valid_device || !$valid_stars($overall) || !$valid_stars($staff) || !$valid_stars($speed)) {
    // Something's missing or the device is unknown/deactivated — send back rather than saving bad data.
    header('Location: kiosk.php?device=' . ($valid_device ? $device_id : 0));
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO responses (device_id, overall_stars, staff_stars, speed_stars)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$device_id, $overall, $staff, $speed]);

header('Location: kiosk.php?device=' . $device_id . '&thanks=1');
exit;

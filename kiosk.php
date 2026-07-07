<?php
require 'config.php';
require 'helpers.php';

$device = (int)($_GET['device'] ?? 0);
if ($device < 1 || $device > 3) {
    $device = 1; // fallback so the page never shows blank if the link is wrong
}
$thanks = isset($_GET['thanks']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Rate Our Service</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="kiosk-body">

<?php if ($thanks): ?>
    <div class="kiosk-thanks" id="thanksScreen">
        <div class="thanks-check">&#10003;</div>
        <h1>Thank you!</h1>
        <p>Your feedback helps us improve.</p>
    </div>
    <script>
        setTimeout(function () {
            window.location.href = 'kiosk.php?device=<?= $device ?>';
        }, 3500);
    </script>
<?php else: ?>

    <div class="kiosk-wrap">
        <div class="kiosk-badge">Station <?= $device ?></div>
        <h1 class="kiosk-title">How was our service today?</h1>
        <p class="kiosk-sub">Tap to rate — takes 10 seconds</p>

        <form id="kioskForm" action="submit_response.php" method="post">
            <input type="hidden" name="device_id" value="<?= $device ?>">
            <input type="hidden" name="overall_stars" id="overall_stars" value="0">
            <input type="hidden" name="staff_stars" id="staff_stars" value="0">
            <input type="hidden" name="speed_stars" id="speed_stars" value="0">

            <div class="kiosk-question">
                <div class="kiosk-q-label">Overall service</div>
                <div class="kiosk-stars" data-target="overall_stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="kiosk-star" data-value="<?= $i ?>">&#9733;</button>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="kiosk-question">
                <div class="kiosk-q-label">Staff friendliness</div>
                <div class="kiosk-stars" data-target="staff_stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="kiosk-star" data-value="<?= $i ?>">&#9733;</button>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="kiosk-question">
                <div class="kiosk-q-label">Speed of service</div>
                <div class="kiosk-stars" data-target="speed_stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" class="kiosk-star" data-value="<?= $i ?>">&#9733;</button>
                    <?php endfor; ?>
                </div>
            </div>

            <button type="submit" id="submitBtn" class="kiosk-submit" disabled>Submit</button>
        </form>
    </div>

    <script>
        const groups = document.querySelectorAll('.kiosk-stars');
        const submitBtn = document.getElementById('submitBtn');
        const answered = { overall_stars: 0, staff_stars: 0, speed_stars: 0 };

        groups.forEach(function (group) {
            const targetId = group.dataset.target;
            const stars = group.querySelectorAll('.kiosk-star');
            stars.forEach(function (star) {
                star.addEventListener('click', function () {
                    const value = parseInt(star.dataset.value, 10);
                    document.getElementById(targetId).value = value;
                    answered[targetId] = value;
                    stars.forEach(function (s) {
                        s.classList.toggle('kiosk-star-filled', parseInt(s.dataset.value, 10) <= value);
                    });
                    checkComplete();
                });
            });
        });

        function checkComplete() {
            const done = Object.values(answered).every(function (v) { return v > 0; });
            submitBtn.disabled = !done;
        }
    </script>

<?php endif; ?>
</body>
</html>

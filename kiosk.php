<?php
require 'config.php';
require 'helpers.php';

$device_id = (int)($_GET['device'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ? AND active = 1");
$stmt->execute([$device_id]);
$device = $stmt->fetch();

if (!$device) {
    // Unknown or deactivated device id — show a clear message instead of a broken form.
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device not set up</title>
    <link rel="stylesheet" href="style.css">
    </head>
    <body class="kiosk-body">
        <div class="kiosk-thanks">
            <h1>This station isn't set up yet</h1>
            <p>Ask an admin to add this device in <code>devices.php</code>, or check the link's <code>?device=</code> number.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
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
            window.location.href = 'kiosk.php?device=<?= (int)$device['id'] ?>';
        }, 3500);
    </script>
<?php else: ?>

    <div class="kiosk-wrap">
        <div class="kiosk-badge"><?= e($device['name']) ?></div>
        <h1 class="kiosk-title">How was our service today?</h1>
        <p class="kiosk-sub">Tap to rate — takes 10 seconds</p>

        <form id="kioskForm" action="submit_response.php" method="post">
            <input type="hidden" name="device_id" value="<?= (int)$device['id'] ?>">
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

            <div class="kiosk-question">
                <div class="kiosk-q-label">Anything you'd like to tell us? <span class="kiosk-optional">(optional)</span></div>
                <textarea name="comment" class="kiosk-comment" placeholder="Type here..." maxlength="500"></textarea>
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

<?php
session_start();

if (!isset($_SESSION['catering_id'])) {
    header("Location: login.php");
    exit();
}

$catering_id = $_SESSION['catering_id'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch notifications for this catering
$stmt = $conn->prepare("SELECT * FROM notifications WHERE catering_id = :id ORDER BY created_at DESC");
$stmt->execute([':id' => $catering_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optionally mark all as read when visiting
$update = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE catering_id = :id");
$update->execute([':id' => $catering_id]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Catering Notifications</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f5f7; padding: 20px; }
.notification-card { background: #fff; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 5px solid #0d6efd; }
.notification-card.unread { font-weight: bold; border-left-color: #dc3545; }
</style>
</head>
<body>

<div class="container">
    <h2 class="mb-4">🔔 Notifications</h2>

    <?php if ($notifications): ?>
        <?php foreach ($notifications as $n): ?>
            <div class="notification-card <?= $n['is_read']==0 ? 'unread' : '' ?>">
                <h5><?= htmlspecialchars($n['title']) ?></h5>
                <p><?= htmlspecialchars($n['message']) ?></p>
                <small class="text-muted"><?= $n['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No notifications yet.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_notifications.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = intval($_GET['id']);

    // Fetch notification
    $stmt = $conn->prepare("SELECT type FROM admin_notifications WHERE notification_id = ?");
    $stmt->execute([$id]);
    $notif = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notif) {
        header("Location: admin_notifications.php");
        exit();
    }

    // Mark as read
    $update = $conn->prepare("UPDATE admin_notifications SET is_read = 1 WHERE notification_id = ?");
    $update->execute([$id]);

    // Redirect based on type
    switch($notif['type']) {
        case 'booking':
            header("Location: bookings.php"); break;
        case 'event':
            header("Location: events.php"); break;
        case 'catering':
            header("Location: manage_catering.php"); break;
        default:
            header("Location: admin_notifications.php");
    }
    exit();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

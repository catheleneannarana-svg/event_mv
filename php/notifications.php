<?php
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f8f8;
    color: #333;
}

/* === HEADER === */
.dashboard-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 90px;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 10;
}
.dashboard-header .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 28px;
    font-weight: 700;
    color: #333;
}
.dashboard-header .logo-img { width: 100px; }
.header-profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}
.profile-link {
    text-decoration: none;
    color: #333;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.profile-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background-color: #dcb6a3;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    color: white;
    font-size: 18px;
    transition: 0.3s;
}
.profile-circle:hover { background-color: #c49b88; }
.profile-name { font-size: 12px; font-weight: 500; text-align: center; }

/* === SIDEBAR === */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 90px;
    left: 0;
    background: #fff;
    color: #333;
    padding-top: 10px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}
.sidebar h2 {
    padding-left: 20px;
    margin-top: 0;
    margin-bottom: 20px;
    font-family: cursive;
    color: #dcb6a3;
    font-size: 28px;
}
.sidebar-nav {
    list-style: none;
    padding: 0 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.sidebar-btn {
    display: block;
    width: 100%;
    padding: 12px 20px;
    text-decoration: none;
    color: #333;
    background-color: #f4f4f4;
    border-radius: 8px;
    font-weight: 500;
    transition: 0.3s;
}
.sidebar-btn:hover { background-color: #dcb6a3; color: #fff; }
.sidebar-btn.active { background-color: #dcb6a3; color: white; font-weight: 600; }

/* === MAIN CONTENT === */
.container {
    margin-left: 260px;
    padding: 20px;
    padding-top: 120px; /* space for header */
}

/* Notification cards */
.notification-card {
    background: #fff;
    border-radius: 10px;
    padding: 15px 20px;
    margin-bottom: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: 0.3s;
}
.notification-card.unread { font-weight: bold; background: #fdf2e9; }
.notification-card:hover { transform: scale(1.02); }
.notification-card small { color: #777; font-weight: normal; display: block; margin-top: 5px; }

h2.page-title { color: #dcb6a3; margin-bottom: 20px; }

</style>
</head>
<body>

<!-- Header -->
<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" alt="EventLink" class="logo-img">
        Event<span style="font-family: 'Dancing Script', cursive; font-size:40px; color: #dcb6a3;">Link</span>
    </div>
    <div class="header-profile">
        <a href="profile.php" class="profile-link">
            <div class="profile-circle"><?= strtoupper(substr($customer['full_name'], 0, 1)) ?></div>
            <div class="profile-name"><?= htmlspecialchars($customer['full_name']) ?></div>
        </a>
    </div>
</header>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="customer_dashboard.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'customer_dashboard.php' ? 'active' : '' ?>">🏠Dashboard</a></li>
        <li><a href="my_bookings.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'active' : '' ?>">📅My Bookings</a></li>
        <li><a href="book_service.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'book_service.php' ? 'active' : '' ?>">🛎️Book Services</a></li>
        <li><a href="catering.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'catering.php' ? 'active' : '' ?>">📅Catering Services</a></li>
        <li><a href="payments.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>">💳Payments</a></li>
        <li><a href="notifications.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">🔔Notifications</a></li>
        <li><a href="logout.php" class="sidebar-btn logout-btn">🚪Logout</a></li>
    </ul>
</div>

<!-- Main content -->
<div class="container">
    <h2 class="page-title">Notifications</h2>

    <?php if (!empty($notifications)): ?>
        <?php foreach ($notifications as $note): ?>
            <div class="notification-card <?= $note['is_read'] == 0 ? 'unread' : '' ?>">
                <?= htmlspecialchars($note['message']) ?>
                <small><?= $note['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No notifications available.</p>
    <?php endif; ?>
</div>

</body>
</html>

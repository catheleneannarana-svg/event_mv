<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all notifications
    $stmt = $conn->query("
        SELECT notification_id, title, message, is_read, created_at, link
        FROM admin_notifications
        ORDER BY created_at DESC
    ");
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Notifications</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link rel="stylesheet" href="../fontawesome/css/all.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    margin-left: 260px;
    background: #f0f2f5;
    color: #333;
    
}

.dashboard-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 90px;
    background: #ffffff; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    z-index: 10;
}

.dashboard-header .logo-img {
    width: 100px;
}

.header-profile {
    font-weight: 600;
    color: #555;
}

.sidebar {
    width: 260px;
    height: calc(100vh - 90px);   
    position: fixed;
    top: 90px;
    left: 0;
    background: #2e3b4e;
    padding-top: 30px;
    box-shadow: 2px 0 5px rgba(0,0,0,.1);
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar h2 {
    padding-left: 20px;
    color: #ffb74d; 
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
    padding: 12px 20px;
    text-decoration: none;
    color: #e0e0e0;
    background: #3b4a5c; 
    border-radius: 8px;
    transition: .3s;
}

.sidebar-btn:hover {
    background: #ffb74d;
    color: #1a1a1a;
}

.sidebar-btn.active {
    background: #ffb74d;
    color: #1a1a1a;
    font-weight: 600;
}

.container { margin-top:100px; padding:20px; max-width:800px; }

.card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 4px 12px rgba(0,0,0,.08); }

.notification { padding:12px; margin-bottom:12px; border-radius:10px; display:block; text-decoration:none; color:#333; transition:.2s; }
.notification.unread { background:#fff3cd; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.notification:hover { background:#e2e3e5; }

.notification .title { font-weight:600; margin:0; }
.notification .message { margin:3px 0; color:#444; }
.notification .date { font-size:12px; color:#555; margin-top:3px; }

.mark-read-btn {
    float:right; padding:7px 12px; border-radius:8px;
    background:#dcb6a3; color:#fff !important;
    text-decoration:none; transition:.3s;
}
.mark-read-btn:hover { background:#c49b88; }

h2 { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
</style>
</head>
<body>
    
<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" class="logo-img">
        Event<span style="font-family:'Dancing Script',cursive;color:#dcb6a3; font-size:40px;"> Link</span>
    </div>

<div class="header-profile">
    <a href="approved_restaurants.php" style="text-decoration: none; color: inherit;">
        <i class="fa fa-utensils"></i> catering
    </a>
</div>

</header>

<!-- SIDEBAR (exact same design) -->
<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php" class="sidebar-btn active"> Dashboard</a></li>
        <a href="themes.php" class="sidebar-btn">Manage Themes</a>
        <a href="manage_catering.php" class="sidebar-btn"> Manage Catering</a>
        <a href="services.php" class="sidebar-btn">Manage Services</a>
        <a href="venues.php" class="sidebar-btn">Manage Venues</a>
        <a href="customers.php" class="sidebar-btn">Customers</a>
        <a href="events.php" class="sidebar-btn">Events</a>
        <a href="bookings.php" class="sidebar-btn">Bookings</a>
        <a href="admin_notifications.php" class="sidebar-btn"> Notifications</a>
        <li><a href="logout.php" class="sidebar-btn">🚪 Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>
        🔔 Notifications
        <a href="#" id="markAllRead" class="mark-read-btn">Mark All as Read</a>
    </h2>

    <div id="notificationList" class="card">
        <?php if($notifications): ?>
            <?php foreach($notifications as $n): ?>
                <a href="<?= $n['link'] ? htmlspecialchars($n['link']) : '#' ?>" 
                   class="notification <?= $n['is_read']==0 ? 'unread' : '' ?>" 
                   data-id="<?= $n['notification_id'] ?>">
                    <p class="title"><?= htmlspecialchars($n['title']) ?></p>
                    <p class="message"><?= htmlspecialchars($n['message']) ?></p>
                    <div class="date"><?= date("M d, Y H:i", strtotime($n['created_at'])) ?></div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; color:#888;">No notifications found.</p>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function(){

    // Mark individual notification as read on click
    $('.notification').on('click', function(e){
        var notif = $(this);
        var id = notif.data('id');

        if(notif.hasClass('unread')) {
            $.post('../mark_notification_read.php', {id: id}, function(response){
                if(response == 'success'){
                    notif.removeClass('unread');
                }
            });
        }
    });

    // Mark all notifications as read
    $('#markAllRead').on('click', function(e){
        e.preventDefault();
        $.post('../mark_all_notifications_read.php', {}, function(response){
            if(response == 'success'){
                $('.notification').removeClass('unread');
            }
        });
    });

});
</script>
</body>
</html>

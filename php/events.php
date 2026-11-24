<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        SELECT  
            e.event_id,
            e.event_name,
            e.guest_count,
            CONCAT(u.first_name, ' ', COALESCE(u.middle_name,''), ' ', u.last_name) AS customer,
            t.theme_name,
            v2.venue_name,
            e.event_date,
            GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services,
            COALESCE(SUM(bd.rate), 0) AS total_rate
        FROM events e
        JOIN users u ON e.customer_id = u.user_id
        LEFT JOIN themes t ON e.theme_id = t.theme_id
        LEFT JOIN venues v2 ON e.venue_id = v2.venue_id
        LEFT JOIN event_services es ON e.event_id = es.event_id
        LEFT JOIN services s ON es.service_id = s.service_id
        LEFT JOIN booking_details bd ON e.event_id = bd.event_id
        WHERE e.status = 'Confirmed'
        GROUP BY e.event_id
        ORDER BY e.event_date ASC
    ";

    $events = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmed Events | EventLink</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link rel="stylesheet" href="../fontawesome/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin:0; padding:0;
    margin-left: 260px;
    background:#f0f2f5;
    color:#333;
}

/* HEADER */
.dashboard-header {
    position: fixed; top:0; left:0; right:0; height:90px;
    background:#fff; display:flex; justify-content:space-between; align-items:center;
    padding:0 30px; box-shadow:0 2px 8px rgba(0,0,0,.1); z-index:10;
}
.dashboard-header .logo-img{ width:100px; }
.dashboard-header .logo span { font-family:'Dancing Script',cursive; color:#dcb6a3; font-size:40px; }
.header-profile{ font-weight:600; color:#555; }

/* SIDEBAR */
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
.sidebar h2{ padding-left:20px; color:#ffb74d; font-size:28px; }
.sidebar-nav { list-style:none; padding:0 10px; display:flex; flex-direction:column; gap:10px; }
.sidebar-btn {
    display:block; padding:12px 20px; text-decoration:none; color:#e0e0e0;
    background:#3b4a5c; border-radius:8px; transition:.3s;
}
.sidebar-btn.active, .sidebar-btn:hover {
    background:#ffb74d; color:#1a1a1a; font-weight:600;
}

/* MAIN CONTAINER */
.container { margin-top:100px; padding:20px; }

/* CARD */
.card {
    border-radius:12px;
    padding:25px 20px;
    background:#fff;
    box-shadow:0 6px 20px rgba(0,0,0,.12);
}

/* CARD HEADER */
.card h2 {
    font-size:28px;
    font-weight:600;
    color:#333;
    margin-bottom:20px;
    display:flex; align-items:center; gap:10px;
}
.card h2 i { color:#ffb74d; }

/* TABLE */
.table {
    border-collapse: separate;
    border-spacing:0;
    border-radius:10px;
    overflow:hidden;
}
.table th, .table td {
    vertical-align: middle !important;
    text-align:center;
    padding:12px 10px;
}
.table thead th {
    background:#ffb74d;
    color:#1a1a1a;
    font-weight:600;
}
.table tbody tr:nth-child(even){ background:#f9f9f9; }
.table tbody tr:hover{ background:#f1f1f1; }
.badge {
    font-size:0.85rem;
    padding:0.4em 0.7em;
    border-radius:12px;
}

/* Scrollable table on small screens */
.table-responsive { overflow-x:auto; }
.logo{ font-size:30px; font-weight:700; }
</style>
</head>
<body>

<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" class="logo-img"> Event<span> Link</span>
    </div>
    <div class="header-profile">Admin</div>
</header>

<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php" class="sidebar-btn active"> Dashboard</a></li>
        <a href="themes.php" class="sidebar-btn">Manage Themes</a>
        <a href="manage_catering.php" class="sidebar-btn"> Manage Catering</a>
        <a href="services.php" class="sidebar-btn">Manage Services</a>
        <a href="venues.php" class="sidebar-btn">Manage venues</a>
        <a href="customers.php" class="sidebar-btn">Customers</a>
        <a href="events.php" class="sidebar-btn">Events</a>
        <a href="bookings.php" class="sidebar-btn">Bookings</a>
        <a href="admin_notifications.php" class="sidebar-btn"> Notifications</a>
        <li><a href="logout.php" class="sidebar-btn">🚪 Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="card">
        <h2><i class="fas fa-calendar-check"></i> Confirmed Events</h2>
        <div class="table-responsive">
        <table class="table table-striped align-middle text-center mb-0">
            <thead>
                <tr>
                    <!-- <th>#</th> -->
                    <th>Customer</th>
                    <th>Event</th>
                    <th>Theme</th>
                    <th>Venue</th>
                    <th>Guests</th>
                    <th>Services</th>
                    <th>Event Date</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($events): ?>
                    <?php foreach($events as $event): ?>
                        <tr>
                            <!-- <td><?= htmlspecialchars($event['event_id']) ?></td> -->
                            <td><?= htmlspecialchars($event['customer']) ?></td>
                            <td><?= htmlspecialchars($event['event_name']) ?></td>
                            <td><?= htmlspecialchars($event['theme_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($event['venue_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($event['guest_count'] ?? '0') ?></td>
                            <td><?= htmlspecialchars($event['services'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($event['event_date']) ?></td>
                            <td>₱<?= number_format($event['total_rate'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center text-muted">No confirmed events found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<script src="../fontawesome/js/all.min.js"></script>
</body>
</html>

<?php
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

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
        e.event_date AS booking_date,
        e.status,
        GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services
    FROM events e
    JOIN users u ON e.customer_id = u.user_id
    LEFT JOIN themes t ON e.theme_id = t.theme_id
    LEFT JOIN venues v2 ON e.venue_id = v2.venue_id
    LEFT JOIN event_services es ON e.event_id = es.event_id
    LEFT JOIN services s ON es.service_id = s.service_id
    WHERE e.status = 'Pending'
    GROUP BY e.event_id
    ORDER BY e.event_date DESC
";

$bookings = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pending Bookings | EventLink</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link rel="stylesheet" href="../fontawesome/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    margin:0; padding:0;
    margin-left:260px;
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
.status-badge { font-weight:500; border-radius:8px; padding:5px 10px; color:#fff; }
.status-pending { background:#ffc107; }
.status-confirmed { background:#2d6a4f; }
.status-cancelled { background:#dc3545; }

/* ACTION BUTTONS */
.btn-sm {
    padding:5px 10px;
    font-size:0.85rem;
    border-radius:6px;
    font-weight:500;
}
.btn-success { background:#2d6a4f; color:#fff; border:none; transition:0.3s; }
.btn-success:hover { background:#1b3a2f; }
.btn-danger { background:#dc3545; color:#fff; border:none; transition:0.3s; }
.btn-danger:hover { background:#a71d2a; }

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
        <h2><i class="fas fa-hourglass-half"></i> Pending Bookings</h2>
        <?php if(count($bookings) === 0): ?>
            <p class="text-muted">No pending bookings at the moment.</p>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-striped align-middle text-center mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Event</th>
                    <th>Theme</th>
                    <th>Venue</th>
                    <th>Guests</th>
                    <th>Services</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['event_id']) ?></td>
                    <td><?= htmlspecialchars($booking['customer']) ?></td>
                    <td><?= htmlspecialchars($booking['event_name']) ?></td>
                    <td><?= htmlspecialchars($booking['theme_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($booking['venue_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($booking['guest_count'] ?? '0') ?></td>
                    <td><?= htmlspecialchars($booking['services'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                            <?= htmlspecialchars($booking['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="update_booking_status.php?id=<?= $booking['event_id'] ?>&action=confirm" class="btn btn-success btn-sm">Confirm</a>
                        <a href="update_booking_status.php?id=<?= $booking['event_id'] ?>&action=cancel" class="btn btn-danger btn-sm">Reject</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<script src="../fontawesome/js/all.min.js"></script>
</body>
</html>

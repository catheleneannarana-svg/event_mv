<?php
session_start();

// Only allow logged-in customers
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
//     header("Location: ../login.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catering Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f5f6fa; }
        .sidebar {
            height: 100vh;
            background: #212529;
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 250px;
        }
        .sidebar a {
            color: white;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            font-size: 15px;
        }
        .sidebar a:hover { background: #343a40; }
        .content { margin-left: 260px; padding: 20px; }
        .card { border-radius: 12px; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h4 class="text-center mb-4">🍽 Catering Panel</h4>

    <a href="catering_dashboard.php">🏠 Dashboard</a>
    <a href="profile_bir.php">👤 Profile & BIR</a>
    <a href="bookings.php">📅 Bookings</a>
    <a href="menu_services.php">📋 Menu & Services</a>
    <a href="notifications.php">🔔 Notifications</a>
    <a href="reports.php">📊 Reports</a>
    <hr class="bg-light">
    <a href="../php/logout.php">🚪 Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <h2 class="mb-3">Dashboard Overview</h2>

    <div class="row g-4">

        <!-- Profile & BIR Preview -->
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h5>👤 Profile & BIR Preview</h5>
                <p>View your personal details and BIR certificate preview.</p>
                <a href="profile_bir.php" class="btn btn-primary">View Details</a>
            </div>
        </div>

        <!-- Bookings -->
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h5>📅 Bookings</h5>
                <p>Accept or decline booking requests from clients.</p>
                <a href="bookings.php" class="btn btn-success">Manage Bookings</a>
            </div>
        </div>

        <!-- Menu & Services -->
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h5>📋 Menu & Services Management</h5>
                <p>Create, update, or delete menu items and services. Upload images.</p>
                <a href="menu_services.php" class="btn btn-warning">Manage Menu</a>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h5>🔔 Notifications</h5>
                <p>View updates, booking alerts, and system notifications.</p>
                <a href="notifications.php" class="btn btn-info">View Notifications</a>
            </div>
        </div>

        <!-- Reports -->
        <div class="col-md-12">
            <div class="card shadow p-4">
                <h5>📊 Simple Reports</h5>
                <p>Revenue, events count, and monthly breakdown overview.</p>
                <a href="reports.php" class="btn btn-dark">View Reports</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>

<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Fetch all services
// Fetch services
$services = $conn->query("SELECT * FROM services ORDER BY service_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch inclusions grouped by service
$inclusions_stmt = $conn->query("SELECT * FROM service_inclusions ORDER BY service_id, inclusion_name ASC");
$all_inclusions = $inclusions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize inclusions by service_id
$inclusions_by_service = [];
foreach ($all_inclusions as $inc) {
    $inclusions_by_service[$inc['service_id']][] = $inc;
}


// Fetch staff count for each service
$staff_count_stmt = $conn->prepare("
    SELECT service_id, COUNT(staff_id) AS staff_count
    FROM staff_services
    GROUP BY service_id
");
$staff_count_stmt->execute();
$staff_counts = $staff_count_stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
// fetch as [service_id => staff_count]

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Services | EventLink</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link rel="stylesheet" href="../fontawesome/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    margin:0; padding:0;
    margin-left: 260px;
    background: #f0f2f5;
    color: #333;
}
/* HEADER */
.dashboard-header {
    position: fixed; top: 0; left:0; right:0; height:90px;
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
.container {
    margin-top:100px; padding:20px;
}

/* CARD */
.card {
    border-radius:12px;
    padding:20px;
    background:#fff;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
}

/* BUTTONS */
.btn-primary {
    background-color:#dcb6a3;
    border-color:#dcb6a3;
    color:white; font-weight:500;
    transition:0.3s;
}
.btn-primary:hover {
    background-color:#c49b88; border-color:#c49b88;
}

/* Action Buttons */
.btn-info { background-color: #ffb74d; color: #1a1a1a; font-weight: 500; border-radius: 6px; border: none; transition: 0.3s; }
.btn-info:hover { background-color: #dcb6a3; color: #1a1a1a; }
.btn-warning { background-color: #ffa500; color: #1a1a1a; font-weight: 500; border-radius: 6px; border: none; transition: 0.3s; }
.btn-warning:hover { background-color: #e6a13a; color: #1a1a1a; }
.btn-danger { background-color: #f56565; color: #fff; font-weight: 500; border-radius: 6px; border: none; transition: 0.3s; }
.btn-danger:hover { background-color: #e04e4e; color: #fff; }

/* TABLE */
.table th, .table td { vertical-align: middle !important; text-align:center; }
.table thead th { background:#f8f9fa; color:#333; }
.table tbody tr:hover { background:#f1f1f1; }
.badge { font-size:0.9rem; padding:0.4em 0.6em; }
.logo{ font-size:30px; font-weight:700; }
.section-header { text-align:left; font-weight:700; font-size:28px; color:#333; border-left:6px solid #dcb6a3; padding-left:10px; margin-bottom:20px; }
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
        <h2 class="mb-4 section-header">🛠 Manage Services</h2>

        <form action="add_service.php" method="POST" class="mb-4 text-end">
            <button type="submit" name="add_service" class="btn btn-primary">➕ Add Service</button>
        </form>

        <table class="table table-striped align-middle text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Rate</th>
                    <th>Staff Assigned</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($services): ?>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td><?= $s['service_id'] ?></td>
                            <td><?= htmlspecialchars($s['service_name']) ?></td>
                            <td><?= htmlspecialchars($s['description']) ?></td>
                            <td>₱<?= number_format($s['rate'],2) ?></td>
                            <td><?= $s['assigned_staff_count'] ?></td>
                            <td>
                                <span class="badge <?= $s['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_service.php?id=<?= $s['service_id'] ?>" class="btn btn-sm btn-info">✏️ Edit</a>
                                <a href="deact_service.php?id=<?= $s['service_id'] ?>" class="btn btn-sm btn-warning">
                                    <?= $s['status']==='active'?'Deactivate':'Activate' ?>
                                </a>
                                <a href="delete_service.php?id=<?= $s['service_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?')">🗑️ Delete</a>
                                <a href="service_inclusions.php?service_id=<?= $s['service_id'] ?>" class="btn btn-sm btn-primary">📌 Manage Inclusions</a>

                            </td>
                            <td>
   


                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-muted">No services available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $search = $_GET['search'] ?? '';

    // Function to build full_name properly
    $fullNameSQL = "CONCAT(first_name,
                     IF(middle_name IS NOT NULL AND middle_name != '', CONCAT(' ', middle_name), ''), 
                     ' ', last_name) AS full_name";

    if ($search) {
        $stmt = $conn->prepare("
            SELECT *, $fullNameSQL
            FROM users
            WHERE (CONCAT(first_name,
                          IF(middle_name IS NOT NULL AND middle_name != '', CONCAT(' ', middle_name), ''), 
                          ' ', last_name) LIKE ? 
                   OR email LIKE ?) 
                  AND role='customer'
            ORDER BY user_id DESC
        ");
        $stmt->execute(["%$search%", "%$search%"]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->query("
            SELECT *, $fullNameSQL
            FROM users
            WHERE role='customer'
            ORDER BY user_id DESC
        ");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Customers | EventLink</title>
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
.sidebar h2{ padding-left:20px; color:#ffb74d; font-size:28px;  }
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
.btn-info, .btn-warning, .btn-danger {
    font-weight:500; border-radius:6px; border:none; transition:0.3s;
}
.btn-info { background-color:#ffb74d; color:#1a1a1a; }
.btn-info:hover { background-color:#dcb6a3; color:#1a1a1a; }
.btn-warning { background-color:#ffa500; color:#1a1a1a; }
.btn-warning:hover { background-color:#e6a13a; color:#1a1a1a; }
.btn-danger { background-color:#f56565; color:#fff; }
.btn-danger:hover { background-color:#e04e4e; color:#fff; }

/* TABLE */
.table th, .table td { vertical-align: middle !important; text-align:center; }
.table thead th { background:#f8f9fa; color:#333; }
.table tbody tr:hover { background:#f1f1f1; }
.badge { font-size:0.9rem; padding:0.4em 0.6em; }
.logo{ font-size:30px; font-weight:700; }
.search-bar input {
    padding:6px 10px; width:200px; border-radius:5px; border:1px solid #ccc; margin-right:5px;
}
.search-bar button {
    padding:10px 10px; border:none; border-radius:5px; background:#dcb6a3; color:#fff; cursor:pointer;
    transition:0.3s;
}
.search-bar button:hover { background:#c49b88; }

.logo{ font-size:30px; font-weight:700; }

/* Add this to your <style> section */
.customer-header {
    text-align: left;
    font-weight: 700; /* bold */
    font-size: 28px;
    color: #333;
}

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
        <!-- Inside the card -->
        <h2 class="mb-4 customer-header">👥 Manage Customers</h2>


        <form method="GET" class="mb-4 text-end search-bar">
            <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <table class="table table-striped align-middle text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers): ?>
                    <?php foreach ($customers as $index => $cust): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($cust['full_name']) ?></td>
                            <td><?= htmlspecialchars($cust['email']) ?></td>
                            <td><?= htmlspecialchars($cust['contact_number']) ?></td>
                            <td>
                                <span class="badge <?= (!isset($cust['status']) || $cust['status']==='active') ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= isset($cust['status']) ? ucfirst($cust['status']) : 'Active' ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_customer.php?id=<?= $cust['user_id'] ?>" class="btn btn-sm btn-info">View</a>
                                <?php if (!isset($cust['status']) || $cust['status'] === 'active'): ?>
                                    <a href="react.php?id=<?= $cust['user_id'] ?>&action=deactivate" class="btn btn-sm btn-warning">Deactivate</a>
                                <?php else: ?>
                                    <a href="react.php?id=<?= $cust['user_id'] ?>&action=activate" class="btn btn-sm btn-warning">Reactivate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-muted">No customers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

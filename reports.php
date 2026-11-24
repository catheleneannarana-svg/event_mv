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
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// --- Total Revenue and Orders ---
$stmt = $conn->prepare("
    SELECT 
        o.order_id,
        m.menu_name AS food_name,
        o.quantity,
        m.price,
        (m.price * o.quantity) AS total
    FROM order_items o
    JOIN catering_menus m ON o.item_id = m.id
    WHERE m.catering_id = :catering_id
    ORDER BY o.order_id DESC
");
$stmt->execute([':catering_id' => $catering_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalRevenue = 0;
$events = [];
foreach ($orders as $order) {
    $totalRevenue += $order['total'];
    $events[$order['order_id']] = true; // unique events
}

// --- Monthly Revenue Breakdown ---
// --- Monthly Revenue Breakdown ---
$stmtMonthly = $conn->prepare("
    SELECT 
        DATE_FORMAT(o2.created_at, '%Y-%m') AS month,
        SUM(cm.price * o1.quantity) AS revenue
    FROM order_items o1
    JOIN catering_menus cm ON o1.item_id = cm.id
    JOIN orders o2 ON o1.order_id = o2.order_id
    WHERE cm.catering_id = :catering_id
    GROUP BY month
    ORDER BY month
");
$stmtMonthly->execute([':catering_id' => $catering_id]);
$monthlyData = $stmtMonthly->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Catering Revenue Report</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { padding: 20px; font-family: Arial; }
.total { font-weight: bold; font-size: 1.2em; }
.monthly-table { max-width: 500px; }
</style>
</head>
<body>
<div class="container">
    <h2 class="mb-4">📊 Catering Revenue Overview</h2>

    <p><strong>Total Revenue:</strong> ₱<?= number_format($totalRevenue, 2) ?></p>
    <p><strong>Total Events:</strong> <?= count($events) ?></p>

    <hr>
    <h4>Monthly Revenue Breakdown</h4>
    <?php if ($monthlyData): ?>
        <table class="table table-bordered monthly-table">
            <thead class="table-dark">
                <tr>
                    <th>Month</th>
                    <th>Revenue (₱)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthlyData as $m): ?>
                <tr>
                    <td><?= $m['month'] ?></td>
                    <td><?= number_format($m['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No orders found yet.</p>
    <?php endif; ?>

    <hr>
    <h4>All Orders</h4>
    <?php if ($orders): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Food Name</th>
                    <th>Quantity</th>
                    <th>Price (₱)</th>
                    <th>Total (₱)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= $o['order_id'] ?></td>
                    <td><?= htmlspecialchars($o['food_name']) ?></td>
                    <td><?= $o['quantity'] ?></td>
                    <td><?= number_format($o['price'], 2) ?></td>
                    <td><?= number_format($o['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-warning">No orders found for your catering yet.</p>
    <?php endif; ?>
</div>
</body>
</html>

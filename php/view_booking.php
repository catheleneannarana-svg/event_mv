<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Booking ID is required.");
}

$bookingId = intval($_GET['id']);

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch booking main data
    $stmt = $conn->prepare("
        SELECT b.*, 
               t.theme_name, t.price AS theme_price,
               v.venue_name, v.location AS venue_location, v.capacity AS venue_capacity
        FROM bookings b
        LEFT JOIN themes t ON b.theme_id = t.theme_id
        LEFT JOIN venues v ON b.venue_id = v.venue_id
        WHERE b.booking_id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        die("Booking not found.");
    }

    // Fetch services
    $stmt2 = $conn->prepare("
        SELECT bs.*, s.service_name, s.rate 
        FROM booking_services bs
        JOIN services s ON bs.service_id = s.service_id
        WHERE bs.booking_id = ?
    ");
    $stmt2->execute([$bookingId]);
    $services = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Fetch inclusions + schedule
    $stmt3 = $conn->prepare("
        SELECT cis.*, si.inclusion_name, si.price AS inclusion_price, s.service_name
        FROM customer_inclusion_schedule cis
        JOIN service_inclusions si ON cis.inclusion_id = si.inclusion_id
        JOIN services s ON si.service_id = s.service_id
        WHERE cis.booking_id = ?
    ");
    $stmt3->execute([$bookingId]);
    $inclusions = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // Fetch catering with correct price from menu_items
    $stmt4 = $conn->prepare("
        SELECT bc.*, mi.name, mi.price 
        FROM booking_catering bc
        JOIN menu_items mi ON bc.menu_id = mi.item_id
        WHERE bc.booking_id = ?
    ");
    $stmt4->execute([$bookingId]);
    $caterings = $stmt4->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Booking</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body { font-family: 'Poppins', sans-serif; background:#f8f8f8; padding:20px; }
.card { background:#fff; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
.table th { background:#dcb6a3; color:#fff; }
</style>
</head>
<body>

<h2 class="mb-4">Booking Details</h2>

<div class="card">
    <h4>Event Information</h4>
    <p><strong>Event Name:</strong> <?= htmlspecialchars($booking['event_name']) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($booking['event_date']) ?></p>
    <p><strong>Time:</strong> <?= htmlspecialchars($booking['event_time']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($booking['status']) ?></p>
    <p><strong>Total Price:</strong> ₱<?= number_format($booking['total_price'], 2) ?></p>
</div>

<div class="card">
    <h4>Theme</h4>
    <?php if ($booking['theme_name']): ?>
        <p><strong><?= htmlspecialchars($booking['theme_name']) ?></strong> — ₱<?= number_format($booking['theme_price'], 2) ?></p>
    <?php else: ?>
        <p>No theme selected.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h4>Venue</h4>
    <?php if ($booking['venue_name']): ?>
        <p><strong><?= htmlspecialchars($booking['venue_name']) ?></strong></p>
        <p>Location: <?= htmlspecialchars($booking['venue_location']) ?></p>
        <p>Capacity: <?= intval($booking['venue_capacity']) ?></p>
    <?php else: ?>
        <p>No venue selected.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h4>Services</h4>
    <?php if ($services): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Service</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['service_name']) ?></td>
                <td>₱<?= number_format($s['rate'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No services selected.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h4>Inclusions & Schedule</h4>
    <?php if ($inclusions): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Service</th>
                <th>Inclusion</th>
                <th>Price</th>
                <th>Scheduled Date</th>
                <th>Scheduled Time</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inclusions as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['service_name']) ?></td>
                <td><?= htmlspecialchars($i['inclusion_name']) ?></td>
                <td>₱<?= number_format($i['inclusion_price'], 2) ?></td>
                <td><?= $i['scheduled_date'] ?: '—' ?></td>
                <td><?= $i['scheduled_time'] ?: '—' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No inclusions selected.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h4>Catering</h4>
    <?php if ($caterings): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Menu Item</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($caterings as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td>₱<?= number_format($c['price'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No catering selected.</p>
    <?php endif; ?>
</div>

<a href="my_bookings.php" class="btn btn-secondary mt-3">Back to My Bookings</a>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

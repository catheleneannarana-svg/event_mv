<?php
session_start();

if (!isset($_SESSION['catering_id'])) {
    header("Location: ../php/login.php");
    exit();
}

$catering_id = $_SESSION['catering_id'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch events related to this catering
    $stmtEvents = $conn->prepare("
        SELECT DISTINCT cbd.event_id, cbd.event_name, cbd.event_date, cbd.user_id
        FROM catering_booking_details cbd
        WHERE cbd.catering_id = :catering_id
        ORDER BY cbd.event_date DESC
    ");
    $stmtEvents->execute([':catering_id' => $catering_id]);
    $events = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all users and build full name
    $stmtUsers = $conn->query("
        SELECT 
            user_id,
            TRIM(
                CONCAT(
                    first_name, ' ',
                    IFNULL(middle_name, ''), ' ',
                    last_name
                )
            ) AS full_name
        FROM users
    ");
    $users = $stmtUsers->fetchAll(PDO::FETCH_KEY_PAIR);

    // Fetch catering menus
    $stmtMenus = $conn->prepare("
        SELECT id, menu_name, price
        FROM catering_menus
        WHERE catering_id = :catering_id
    ");
    $stmtMenus->execute([':catering_id' => $catering_id]);
    $menus = $stmtMenus->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Catering Bookings & Menus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { padding: 20px; font-family: Arial; }
h4 { margin-top: 20px; }
</style>
</head>
<body>
<div class="container">
    <h2>Customer Orders for Your Catering</h2>

    <?php foreach ($events as $event): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h4>
                    Event: <?= htmlspecialchars($event['event_name']) ?> |
                    Date: <?= htmlspecialchars($event['event_date']) ?>
                </h4>

                <p>
                    <strong>Customer:</strong>
                    <?= htmlspecialchars($users[$event['user_id']] ?? 'Unknown') ?>
                </p>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Menu Item</th>
                            <th>Price (₱)</th>
                            <th>Total (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['menu_name']) ?></td>
                                <td><?= number_format($m['price'], 2) ?></td>
                                <td><?= number_format($m['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>

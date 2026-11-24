<?php
session_start();

// Redirect if not logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

$error = '';

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure previous selections exist
    if (!isset($_SESSION['selected_theme']) || empty($_SESSION['selected_services'])) {
        header("Location: book_service.php");
        exit();
    }

    // Fetch selections
    $selectedTheme = null;
    $selectedServices = [];
    $selectedCaterings = [];
    $selectedVenue = null;
    $selectedInclusions = $_SESSION['selected_inclusions'] ?? [];
    $selectedSchedule = $_SESSION['selected_schedule'] ?? [];

    // Fetch Theme
    if (!empty($_SESSION['selected_theme'])) {
        $stmt = $conn->prepare("SELECT * FROM themes WHERE theme_id=? LIMIT 1");
        $stmt->execute([$_SESSION['selected_theme']]);
        $selectedTheme = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch Services
    if (!empty($_SESSION['selected_services'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['selected_services']), '?'));
        $stmt = $conn->prepare("SELECT * FROM services WHERE service_id IN ($placeholders)");
        $stmt->execute($_SESSION['selected_services']);
        $selectedServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch Caterings
    if (!empty($_SESSION['selected_catering'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['selected_catering']), '?'));
        $stmt = $conn->prepare("SELECT * FROM menu_items WHERE item_id IN ($placeholders)");
        $stmt->execute($_SESSION['selected_catering']);
        $selectedCaterings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch Venue
    if (!empty($_SESSION['selected_venue'])) {
        $stmt = $conn->prepare("SELECT * FROM venues WHERE venue_id=? LIMIT 1");
        $stmt->execute([$_SESSION['selected_venue']]);
        $selectedVenue = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Calculate Total
    $total = 0;
    if ($selectedTheme) $total += $selectedTheme['price'] ?? 0;

    foreach ($selectedServices as $s) {
        $serviceId = $s['service_id'];
        $serviceRate = $s['rate'] ?? 0;
        $inclusionPrice = 0;

        if (!empty($selectedInclusions[$serviceId])) {
            $stmtInc = $conn->prepare("SELECT price FROM service_inclusions WHERE inclusion_id=? LIMIT 1");
            $stmtInc->execute([$selectedInclusions[$serviceId]]);
            $inc = $stmtInc->fetch(PDO::FETCH_ASSOC);
            $inclusionPrice = $inc['price'] ?? 0;
        }

        $total += $serviceRate + $inclusionPrice;
    }

    foreach ($selectedCaterings as $c) {
        $total += $c['price'] ?? 0;
    }

    // ===== Handle Confirm Booking =====
    if (isset($_POST['confirm_booking'])) {
        $event_name = trim($_POST['event_name'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $guest_count = (int)($_POST['guest_count'] ?? 0);

        // Determine venue_id
        $venue_id = $_POST['venue_id'] ?? null;
        if ((!$venue_id || $venue_id == '') && !empty($_POST['venue']) && !empty($_POST['venue_location'])) {
            $stmt = $conn->prepare("INSERT INTO venues (venue_name, location) VALUES (?, ?)");
            $stmt->execute([$_POST['venue'], $_POST['venue_location']]);
            $venue_id = $conn->lastInsertId();
        }

        // Validate required fields
        if (empty($event_name) || empty($event_date) || empty($event_time) || $guest_count <= 0 || !$venue_id) {
            $error = "Please fill all fields and provide a valid guest count/venue.";
        } else {
            // Insert booking
            $stmt = $conn->prepare("
                INSERT INTO bookings 
                (user_id, event_name, event_date, event_time, theme_id, venue_id, total_price, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
            ");
            $stmt->execute([$user_id, $event_name, $event_date, $event_time, $selectedTheme['theme_id'] ?? null, $venue_id, $total]);
            $booking_id = $conn->lastInsertId();

            // Insert services
            $stmtService = $conn->prepare("INSERT INTO booking_services (booking_id, service_id) VALUES (?, ?)");
            foreach ($_SESSION['selected_services'] as $sid) {
                $stmtService->execute([$booking_id, $sid]);
            }

            // Insert inclusions with schedule (only if inclusion exists)
            if (!empty($_SESSION['selected_inclusions'])) {
                $stmtInclusion = $conn->prepare("
                    INSERT INTO customer_inclusion_schedule 
                    (booking_id, inclusion_id, scheduled_date, scheduled_time, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");

                foreach ($_SESSION['selected_inclusions'] as $serviceId => $incId) {
                    // Verify inclusion exists to avoid FK errors
                    $stmtCheck = $conn->prepare("SELECT inclusion_id FROM service_inclusions WHERE inclusion_id=? LIMIT 1");
                    $stmtCheck->execute([$incId]);
                    if ($stmtCheck->fetchColumn()) {
                        $day = $_SESSION['selected_schedule'][$serviceId]['day'] ?? null;
                        $time = $_SESSION['selected_schedule'][$serviceId]['time'] ?? null;
                        $stmtInclusion->execute([$booking_id, $incId, $day, $time, '']);
                    }
                }
            }

            // Insert catering
            if (!empty($_SESSION['selected_catering'])) {
                $stmtCatering = $conn->prepare("INSERT INTO booking_catering (booking_id, menu_id) VALUES (?, ?)");
                foreach ($_SESSION['selected_catering'] as $cid) {
                    // Optional check to avoid FK error
                    $stmtCheck = $conn->prepare("SELECT item_id FROM menu_items WHERE item_id=? LIMIT 1");
                    $stmtCheck->execute([$cid]);
                    if ($stmtCheck->fetchColumn()) {
                        $stmtCatering->execute([$booking_id, $cid]);
                    }
                }

            }

            // Clear sessions
            unset($_SESSION['selected_theme'], $_SESSION['selected_services'], $_SESSION['selected_catering'], $_SESSION['selected_venue'], $_SESSION['selected_inclusions'], $_SESSION['selected_schedule']);

            // Redirect
            header("Location: my_bookings.php");
            exit();
        }
    }

} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout | Event Management</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body { font-family: 'Poppins', sans-serif; background:#f8f8f8; padding:20px; }
.card { background:#fff; border-radius:10px; padding:15px; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
.selection-card { max-height:250px; overflow-y:auto; padding-right:10px;}
.btn-primary { background:#dcb6a3; border:none; }
.btn-primary:hover { background:#c49b88; }
.btn-secondary { background:#6c757d; color:#fff; border:none; }
</style>
</head>
<body>

<h2>Checkout / Confirm Booking</h2>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <div class="row">
        <!-- Event Details -->
        <div class="col-md-6">
            <div class="card">
                <h4>Event Details</h4>
                <div class="mb-2">
                    <label>Event Name</label>
                    <input type="text" name="event_name" class="form-control" value="<?= htmlspecialchars($_POST['event_name'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label>Date</label>
                    <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label>Time</label>
                    <input type="time" name="event_time" class="form-control" value="<?= htmlspecialchars($_POST['event_time'] ?? '') ?>" required>
                </div>
                <div class="mb-2">
                    <label>Guest Count</label>
                    <input type="number" name="guest_count" min="1" class="form-control" value="<?= htmlspecialchars($_POST['guest_count'] ?? '') ?>" required>
                </div>

                <!-- Venue -->
                <?php if ($selectedVenue): ?>
                    <input type="hidden" name="venue_id" value="<?= intval($selectedVenue['venue_id']) ?>">
                <?php else: ?>
                    <div class="mb-2">
                        <label>Venue Name</label>
                        <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($_POST['venue'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label>Venue Location</label>
                        <input type="text" name="venue_location" class="form-control" value="<?= htmlspecialchars($_POST['venue_location'] ?? '') ?>" required>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Selections -->
        <div class="col-md-6">
            <div class="card">
                <h4>Your Selections</h4>

                <div class="card mb-2 selection-card">
                    <h5>Theme</h5>
                    <p><?= htmlspecialchars($selectedTheme['theme_name']) ?> - ₱<?= number_format($selectedTheme['price'],2) ?></p>
                </div>

                <div class="card mb-2 selection-card">
                    <h5>Services</h5>
                    <?php foreach ($selectedServices as $s):
                        $serviceId = $s['service_id'];
                        $serviceRate = $s['rate'] ?? 0;
                        $inclusionName = '';
                        $inclusionPrice = 0;
                        $scheduleDay = '';
                        $scheduleTime = '';

                        // Inclusion info
                        if (!empty($selectedInclusions[$serviceId])) {
                            $stmtInc = $conn->prepare("SELECT * FROM service_inclusions WHERE inclusion_id=? LIMIT 1");
                            $stmtInc->execute([$selectedInclusions[$serviceId]]);
                            $inc = $stmtInc->fetch(PDO::FETCH_ASSOC);
                            if ($inc) {
                                $inclusionName = $inc['inclusion_name'];
                                $inclusionPrice = $inc['price'] ?? 0;
                            }
                        }

                        // Schedule info
                        if (!empty($selectedSchedule[$serviceId])) {
                            $schedule = $selectedSchedule[$serviceId];
                            $scheduleDay = !empty($schedule['day']) ? date('M d, Y', strtotime($schedule['day'])) : '';
                            $scheduleTime = !empty($schedule['time']) ? date('h:i A', strtotime($schedule['time'])) : '';
                        }
                    ?>
                        <p>
                            <?= htmlspecialchars($s['service_name']) ?> - ₱<?= number_format($serviceRate,2) ?>
                            <?php if($inclusionName): ?>
                                <br><small>Inclusion: <?= htmlspecialchars($inclusionName) ?> - ₱<?= number_format($inclusionPrice,2) ?></small>
                            <?php endif; ?>
                            <?php if($scheduleDay || $scheduleTime): ?>
                                <br><small>Schedule: <?= $scheduleDay ?: 'N/A' ?> <?= $scheduleTime ?: '' ?></small>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($selectedCaterings)): ?>
                <div class="card mb-2 selection-card">
                    <h5>Catering</h5>
                    <?php foreach($selectedCaterings as $c): ?>
                        <p><?= htmlspecialchars($c['name']) ?> - ₱<?= number_format($c['price'],2) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($selectedVenue): ?>
                <div class="card mb-2 selection-card">
                    <h5>Venue</h5>
                    <p><strong><?= htmlspecialchars($selectedVenue['venue_name']) ?></strong></p>
                    <p>Location: <?= htmlspecialchars($selectedVenue['location']) ?></p>
                    <p>Capacity: <?= intval($selectedVenue['capacity']) ?></p>
                </div>
                <?php endif; ?>

                <h4 class="text-end">Total: ₱<?= number_format($total,2) ?></h4>

                <div class="text-end mt-2">
                    <button type="submit" name="confirm_booking" class="btn btn-primary">Confirm Booking</button>
                    <button type="submit" name="cancel_booking" class="btn btn-secondary" formnovalidate>Cancel</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

// --- Safe defaults for session-selected items (prevents undefined/index errors) ---
if (!isset($_SESSION['selected_theme'])) {
    $_SESSION['selected_theme'] = null; // store theme_id or null
}
if (!isset($_SESSION['selected_services']) || !is_array($_SESSION['selected_services'])) {
    $_SESSION['selected_services'] = [];
}
if (!isset($_SESSION['selected_catering']) || !is_array($_SESSION['selected_catering'])) {
    $_SESSION['selected_catering'] = [];
}

// Redirect if not logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // If NOT coming from the sidebar, require prior selections
    if (
        !isset($_GET['from_sidebar']) && (
            empty($_SESSION['selected_theme']) ||
            count($_SESSION['selected_services']) === 0
        )
    ) {
        header("Location: checkout.php");
        exit();
    }

    // Handle POST requests early
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Standard venue selection
        if (isset($_POST['venue_id'])) {
            $_SESSION['selected_venue'] = intval($_POST['venue_id']);
            header("Location: checkout.php");
            exit();
        }

        // Skip venue
        if (isset($_POST['skip_venue'])) {
            unset($_SESSION['selected_venue']); // venue not selected
            header("Location: confirm_booking.php");
            exit();
        }
    }

    // Fetch selected theme only if set
    $selected_theme = null;
    if (!empty($_SESSION['selected_theme'])) {
        $stmt = $conn->prepare("SELECT * FROM themes WHERE theme_id = ? LIMIT 1");
        $stmt->execute([$_SESSION['selected_theme']]);
        $selected_theme = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // Fetch selected services only if array has values
    $selected_services = [];
    if (count($_SESSION['selected_services']) > 0) {
        // build placeholders
        $placeholders = implode(',', array_fill(0, count($_SESSION['selected_services']), '?'));
        $stmt = $conn->prepare("SELECT * FROM services WHERE service_id IN ($placeholders)");
        $stmt->execute($_SESSION['selected_services']);
        $selected_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch selected catering items only if present
    $selected_catering = [];
    if (count($_SESSION['selected_catering']) > 0) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['selected_catering']), '?'));
        $stmt = $conn->prepare("SELECT * FROM menu_items WHERE item_id IN ($placeholders)");
        $stmt->execute($_SESSION['selected_catering']);
        $selected_catering = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch all venues
    $venues = $conn->query("SELECT * FROM venues ORDER BY venue_name")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch booked venues (flatten to array)
    $bookedStmt = $conn->query("SELECT venue_id FROM bookings WHERE status IN ('Confirmed','Pending')");
    $bookedVenues = $bookedStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Connection failed: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Select Venue</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body { font-family: 'Poppins', sans-serif; background:#f8f8f8; padding:20px; color:#333; }
.card { background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:15px; transition:0.3s; }
.card:hover { transform:scale(1.02); }
.btn-primary { background-color:#dcb6a3; border-color:#dcb6a3; color:white; font-weight:700; transition:0.3s; }
.btn-primary:hover { background-color:#c49b88; border-color:#c49b88; }
.btn-secondary { background:#6c757d; color:white; font-weight:700; }
.btn-primary[disabled] { background:#ccc; cursor:not-allowed; }
.carousel-item img { border-radius:10px; height:200px; object-fit:cover; }
</style>
</head>
<body>

<div class="container mt-4">

    <!-- Selected Theme -->
    <h2 class="mb-3">Selected Theme</h2>
    <div class="card mb-3 p-3">
        <?php if ($selected_theme): ?>
            <h5><?= htmlspecialchars($selected_theme['theme_name']) ?></h5>
            <p><?= htmlspecialchars($selected_theme['description']) ?></p>
            <p><strong>Price:</strong> ₱<?= number_format($selected_theme['price'] ?? 0,2) ?></p>
        <?php else: ?>
            <p class="text-muted">No theme selected yet.</p>
        <?php endif; ?>
    </div>

    <!-- Selected Services -->
    <!-- Selected Services -->
<h2 class="mb-3">Selected Services</h2>
<div class="row mb-3">
    <?php if (!empty($selected_services)): ?>
        <?php foreach($selected_services as $service): ?>
            <?php 
                $serviceId = $service['service_id'];
                $inclusionId = $_SESSION['selected_inclusions'][$serviceId] ?? null;
                $schedule = $_SESSION['selected_schedule'][$serviceId] ?? ['day'=>'','time'=>''];

                // Fetch inclusion info if selected
                $inclusionName = '';
                $inclusionPrice = 0;
                if ($inclusionId) {
                    $stmt = $conn->prepare("SELECT * FROM service_inclusions WHERE inclusion_id = ? LIMIT 1");
                    $stmt->execute([$inclusionId]);
                    $inc = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($inc) {
                        $inclusionName = $inc['inclusion_name'];
                        $inclusionPrice = $inc['price'];
                    }
                }
            ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100 p-3">
                    <h5><?= htmlspecialchars($service['service_name']) ?></h5>
                    <p><?= htmlspecialchars($service['description']) ?></p>
                    <p><strong>Rate:</strong> ₱<?= number_format($service['rate'] ?? 0,2) ?></p>

                    <?php if ($inclusionName): ?>
                        <p><strong>Inclusion:</strong> <?= htmlspecialchars($inclusionName) ?> (₱<?= number_format($inclusionPrice,2) ?>)</p>
                    <?php endif; ?>

                    <?php if (!empty($schedule['day']) || !empty($schedule['time'])): ?>
                        <p><strong>Schedule:</strong> 
                            <?= !empty($schedule['day']) ? date('M d, Y', strtotime($schedule['day'])) : 'N/A' ?> 
                            <?= !empty($schedule['time']) ? 'at ' . date('h:i A', strtotime($schedule['time'])) : '' ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <p class="text-muted">No services selected.</p>
        </div>
    <?php endif; ?>
</div>


    <!-- Selected Catering -->
    <h2 class="mb-3">Selected Catering</h2>
    <div class="row mb-3">
        <?php if (!empty($selected_catering)): ?>
            <?php foreach($selected_catering as $menu): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 p-3">
                        <h5><?= htmlspecialchars($menu['name']) ?></h5>
                        <p><?= htmlspecialchars($menu['description']) ?></p>
                        <p><strong>Price:</strong> ₱<?= number_format($menu['price'] ?? 0,2) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-muted">No catering items selected.</p>
            </div>
        <?php endif; ?>
    </div>

    

    <!-- Available Venues -->
    <h2 class="mb-3">Available Venues</h2>
    <div class="text-center mb-4">
        <form method="post" action="all_venues.php">
            <button type="submit" name="skip_venue" class="btn btn-secondary w-50">Skip / Enter Own Venue</button>
        </form>
    </div>
    <?php if (!empty($venues)): ?>
    <form method="post" action="all_venues.php">
        <div class="row">
            <?php foreach($venues as $venue): 
                $isBooked = in_array($venue['venue_id'], $bookedVenues);
                $disabled = $isBooked ? 'disabled' : '';
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 p-3">
                    <?php if (!empty($venue['image'])): 
                        $venueImages = array_filter(array_map('trim', explode(',', $venue['image'])));
                        $first = true;
                    ?>
                    <div id="venueCarousel<?= (int)$venue['venue_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach($venueImages as $img): ?>
                                <?php $imgFile = basename($img); // encode only filename ?>
                                <div class="carousel-item <?= $first ? 'active' : '' ?>">
                                    <img src="../uploads/venues/<?= rawurlencode($imgFile) ?>" class="d-block w-100" alt="<?= htmlspecialchars($venue['venue_name']) ?>">
                                </div>
                            <?php $first = false; endforeach; ?>
                        </div>
                        <?php if(count($venueImages)>1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#venueCarousel<?= (int)$venue['venue_id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#venueCarousel<?= (int)$venue['venue_id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <h5><?= htmlspecialchars($venue['venue_name']) ?></h5>
                    <p><strong>Location:</strong> <?= htmlspecialchars($venue['location']) ?></p>
                    <p><strong>Capacity:</strong> <?= intval($venue['capacity']) ?></p>
                    <div class="text-center mt-2">
                        <button type="submit" name="venue_id" value="<?= (int)$venue['venue_id'] ?>" class="btn btn-primary w-100" <?= $disabled ?>>
                            <?= $isBooked ? 'Unavailable' : 'Select Venue' ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </form>
    <?php else: ?>
        <p class="text-danger">No venues are currently available.</p>
    <?php endif; ?>

</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

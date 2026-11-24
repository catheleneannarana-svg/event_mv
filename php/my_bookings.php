<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = (int) $_SESSION['user_id'];

    // Fetch bookings for this user (most recent first)
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bookingIds = array_column($bookings, 'booking_id');

    // Prepare containers
    $servicesByBooking = [];
    $inclusionsByBooking = [];
    $cateringsByBooking = [];
    $themesMap = [];
    $venuesMap = [];

    if (!empty($bookingIds)) {
        // --- Fetch booking_services and service details ---
        $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));

        // Get booking_services
        $stmt = $conn->prepare("SELECT bs.booking_service_id, bs.booking_id, bs.service_id, s.service_name, s.rate AS service_rate
                                FROM booking_services bs
                                LEFT JOIN services s ON bs.service_id = s.service_id
                                WHERE bs.booking_id IN ($placeholders)
                                ORDER BY bs.booking_service_id ASC");
        $stmt->execute($bookingIds);
        $svcRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($svcRows as $r) {
            $bid = $r['booking_id'];
            $servicesByBooking[$bid][] = $r;
        }

        // --- Fetch customer_inclusion_schedule with inclusion info ---
        $stmt = $conn->prepare("SELECT cis.schedule_id, cis.booking_id, cis.inclusion_id, cis.scheduled_date, cis.scheduled_time, cis.notes,
                                       si.inclusion_name, si.price AS inclusion_price, si.service_id AS inclusion_service_id
                                FROM customer_inclusion_schedule cis
                                LEFT JOIN service_inclusions si ON cis.inclusion_id = si.inclusion_id
                                WHERE cis.booking_id IN ($placeholders)
                                ORDER BY cis.schedule_id ASC");
        $stmt->execute($bookingIds);
        $incRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($incRows as $r) {
            $bid = $r['booking_id'];
            $inclusionsByBooking[$bid][] = $r;
        }

        // --- Fetch booking_caterings with menu item info ---
        $stmt = $conn->prepare("SELECT bc.booking_catering_id, bc.booking_id, bc.menu_id, mi.name AS menu_name, mi.price AS menu_price
                                FROM booking_catering bc
                                LEFT JOIN menu_items mi ON bc.menu_id = mi.item_id
                                WHERE bc.booking_id IN ($placeholders)
                                ORDER BY bc.booking_catering_id ASC");
        $stmt->execute($bookingIds);
        $catRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($catRows as $r) {
            $bid = $r['booking_id'];
            $cateringsByBooking[$bid][] = $r;
        }

        // --- Optional: fetch themes and venues referenced by bookings in batch to avoid repeated queries ---
        // Collect theme and venue ids
        $themeIds = [];
        $venueIds = [];
        foreach ($bookings as $b) {
            if (!empty($b['theme_id'])) $themeIds[] = (int)$b['theme_id'];
            if (!empty($b['venue_id'])) $venueIds[] = (int)$b['venue_id'];
        }
        $themeIds = array_values(array_unique($themeIds));
        $venueIds = array_values(array_unique($venueIds));

        if (!empty($themeIds)) {
            $ph = implode(',', array_fill(0, count($themeIds), '?'));
            $stmt = $conn->prepare("SELECT * FROM themes WHERE theme_id IN ($ph)");
            $stmt->execute($themeIds);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $themesMap[$r['theme_id']] = $r;
        }

        if (!empty($venueIds)) {
            $ph = implode(',', array_fill(0, count($venueIds), '?'));
            $stmt = $conn->prepare("SELECT * FROM venues WHERE venue_id IN ($ph)");
            $stmt->execute($venueIds);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) $venuesMap[$r['venue_id']] = $r;
        }
    }

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>My Bookings</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body { font-family: 'Poppins',sans-serif; background:#f8f8f8; padding:20px; color:#333; }
.card { background:#fff; border-radius:10px; padding:18px; margin-bottom:18px; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
.badge-status { padding:6px 10px; border-radius:12px; font-weight:600; }
.badge-pending { background:#f0ad4e; color:#fff; }
.badge-confirmed { background:#28a745; color:#fff; }
.badge-cancelled { background:#dc3545; color:#fff; }
.small { font-size:0.9rem; color:#555; }
.section-title { font-size:1.05rem; margin-bottom:8px; font-weight:600; }
.row-flex { display:flex; gap:10px; flex-wrap:wrap; }
.col-2 { width:18%; }
.col-3 { width:30%; }
.col-4 { width:40%; }
.right { text-align:right; }
</style>
</head>
<body>
<h2>My Bookings</h2>

<?php if (empty($bookings)): ?>
    <div class="card">
        <p class="small">You have no bookings yet.</p>
        <a href="book_service.php" class="btn btn-primary">Book a service</a>
    </div>
<?php else: ?>

    <?php foreach ($bookings as $booking): 
        $bid = $booking['booking_id'];
        $status = strtolower($booking['status'] ?? '');
        $statusClass = $status === 'pending' ? 'badge-pending' : ($status === 'confirmed' ? 'badge-confirmed' : 'badge-cancelled');
        ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                <div>
                    <h4><?= htmlspecialchars($booking['event_name'] ?: 'Untitled Event') ?></h4>
                    <div class="small">
                        <strong>Booking ID:</strong> <?= (int)$bid ?> 
                        &nbsp;|&nbsp; <strong>Created:</strong> <?= date('M d, Y h:i A', strtotime($booking['created_at'])) ?>
                    </div>
                </div>
                <div class="right">
                    <div class="badge-status <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($booking['status'])) ?></div>
                    <div class="small" style="margin-top:8px;">
                        Total: <strong>₱<?= number_format($booking['total_price'] ?? 0, 2) ?></strong>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row-flex">
                <div class="col-3">
                    <div class="section-title">Event</div>
                    <p class="small">
                        <strong>Date:</strong> <?= !empty($booking['event_date']) ? date('M d, Y', strtotime($booking['event_date'])) : 'N/A' ?><br>
                        <strong>Time:</strong> <?= !empty($booking['event_time']) ? date('h:i A', strtotime($booking['event_time'])) : 'N/A' ?><br>
                        <?php if(!empty($booking['guest_count'])): ?>
                            <strong>Guests:</strong> <?= (int)$booking['guest_count'] ?><br>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="col-3">
                    <div class="section-title">Theme</div>
                    <?php $th = $themesMap[$booking['theme_id']] ?? null; ?>
                    <?php if ($th): ?>
                        <p class="small"><?= htmlspecialchars($th['theme_name']) ?> — ₱<?= number_format($th['price'] ?? 0, 2) ?></p>
                    <?php else: ?>
                        <p class="small">No theme</p>
                    <?php endif; ?>
                </div>

                <div class="col-4">
                    <div class="section-title">Venue</div>
                    <?php $vn = $venuesMap[$booking['venue_id']] ?? null; ?>
                    <?php if ($vn): ?>
                        <p class="small"><strong><?= htmlspecialchars($vn['venue_name']) ?></strong><br>
                        <?= htmlspecialchars($vn['location']) ?><br>
                        Capacity: <?= (int)($vn['capacity'] ?? 0) ?></p>
                    <?php else: ?>
                        <p class="small">Custom or no venue selected</p>
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <div>
                <div class="section-title">Services & Inclusions</div>
                <?php if (!empty($servicesByBooking[$bid])): ?>
                    <?php foreach ($servicesByBooking[$bid] as $svc): 
                        $svcId = $svc['service_id'];
                        $svcName = $svc['service_name'] ?? 'Service #' . $svcId;
                        $svcRate = $svc['service_rate'] ?? 0;

                        // find inclusion for this booking & service (if any)
                        $serviceInclusion = null;
                        if (!empty($inclusionsByBooking[$bid])) {
                            foreach ($inclusionsByBooking[$bid] as $incRow) {
                                // some inclusions may not contain inclusion_service_id; we attempt to match by service if available
                                if (!empty($incRow['inclusion_service_id']) && (int)$incRow['inclusion_service_id'] === (int)$svcId) {
                                    $serviceInclusion = $incRow;
                                    break;
                                }
                                // fallback: if inclusion exists but no service match, still show it (rare)
                            }
                        }
                    ?>
                        <div style="padding:10px 0; border-bottom:1px dashed #eee;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <strong><?= htmlspecialchars($svcName) ?></strong>
                                    <div class="small">Rate: ₱<?= number_format($svcRate,2) ?></div>
                                </div>
                                <div class="small right">Service ID: <?= (int)$svcId ?></div>
                            </div>

                            <?php if ($serviceInclusion): ?>
                                <div style="margin-top:8px;" class="small">
                                    <strong>Inclusion:</strong> <?= htmlspecialchars($serviceInclusion['inclusion_name'] ?? 'N/A') ?> 
                                    — ₱<?= number_format($serviceInclusion['inclusion_price'] ?? 0,2) ?>
                                    <br>
                                    <strong>Scheduled:</strong> 
                                    <?= !empty($serviceInclusion['scheduled_date']) ? date('M d, Y', strtotime($serviceInclusion['scheduled_date'])) : 'N/A' ?> 
                                    <?= !empty($serviceInclusion['scheduled_time']) ? ' at ' . date('h:i A', strtotime($serviceInclusion['scheduled_time'])) : '' ?>
                                    <?php if (!empty($serviceInclusion['notes'])): ?>
                                        <br><strong>Notes:</strong> <?= htmlspecialchars($serviceInclusion['notes']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div style="margin-top:8px;" class="small text-muted">No inclusion scheduled for this service.</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="small text-muted">No services recorded for this booking.</p>
                <?php endif; ?>
            </div>

            <hr>

            <div>
                <div class="section-title">Catering Items</div>
                <?php if (!empty($cateringsByBooking[$bid])): ?>
                    <ul class="small">
                        <?php foreach ($cateringsByBooking[$bid] as $ct): ?>
                            <li>
                                <?= htmlspecialchars($ct['menu_name'] ?? 'Menu #' . ($ct['menu_id'] ?? '')) ?> 
                                — ₱<?= number_format($ct['menu_price'] ?? 0, 2) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="small text-muted">No catering items added.</p>
                <?php endif; ?>
            </div>

            <hr>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div class="small">Booking created: <?= date('M d, Y h:i A', strtotime($booking['created_at'])) ?></div>
                <div>
                    <a href="view_booking.php?id=<?= (int)$bid ?>" class="btn btn-secondary btn-sm">View</a>
                    <!-- You can add Cancel / Download / Print actions here -->
                </div>
            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>

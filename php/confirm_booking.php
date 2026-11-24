<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];

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

    // --- Theme ---
    if (!empty($_SESSION['selected_theme'])) {
        $stmt = $conn->prepare("SELECT * FROM themes WHERE theme_id=? LIMIT 1");
        $stmt->execute([$_SESSION['selected_theme']]);
        $selectedTheme = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- Services ---
    if (!empty($_SESSION['selected_services'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['selected_services']), '?'));
        $stmt = $conn->prepare("SELECT * FROM services WHERE service_id IN ($placeholders)");
        $stmt->execute($_SESSION['selected_services']);
        $selectedServices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Caterings ---
    if (!empty($_SESSION['selected_catering'])) {
        $placeholders = implode(',', array_fill(0, count($_SESSION['selected_catering']), '?'));
        $stmt = $conn->prepare("SELECT * FROM menu_items WHERE item_id IN ($placeholders)");
        $stmt->execute($_SESSION['selected_catering']);
        $selectedCaterings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- Venue ---
    if (!empty($_SESSION['selected_venue'])) {
        $stmt = $conn->prepare("SELECT * FROM venues WHERE venue_id=? LIMIT 1");
        $stmt->execute([$_SESSION['selected_venue']]);
        $selectedVenue = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- Handle Confirm Booking ---
    if (isset($_POST['confirm_booking'])) {
        $event_name = trim($_POST['event_name'] ?? '');
        $event_date = $_POST['event_date'] ?? '';
        $event_time = $_POST['event_time'] ?? '';
        $guest_count = (int)($_POST['guest_count'] ?? 0);

        // Determine venue_id
        $venue_id = $_POST['venue_id'] ?? null;
        if ((!$venue_id || $venue_id == '') && !empty($_POST['venue']) && !empty($_POST['venue_location'])) {
            // Insert custom venue
            $stmt = $conn->prepare("INSERT INTO venues (venue_name, location) VALUES (?, ?)");
            $stmt->execute([$_POST['venue'], $_POST['venue_location']]);
            $venue_id = $conn->lastInsertId();
        }

        // Validate required fields
        if (empty($event_name) || empty($event_date) || empty($event_time) || $guest_count <= 0 || !$venue_id) {
            $error = "Please fill all fields and provide a valid guest count/venue.";
        } else {
            // Calculate total
            $total = 0;
            if ($selectedTheme) $total += $selectedTheme['price'] ?? 0;
            foreach ($selectedServices as $s) $total += $s['rate'] ?? 0;
            foreach ($selectedCaterings as $c) $total += $c['price'] ?? 0;

            // Insert booking
            $stmt = $conn->prepare("
                INSERT INTO bookings
                (user_id, event_name, event_date, event_time, theme_id, venue_id, total_price, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
            ");
            $stmt->execute([
                $user_id, $event_name, $event_date, $event_time,
                $selectedTheme['theme_id'] ?? null, $venue_id, $total
            ]);
            $booking_id = $conn->lastInsertId();

            // Insert booking services
            $stmtService = $conn->prepare("INSERT INTO booking_services (booking_id, service_id) VALUES (?, ?)");
            foreach ($_SESSION['selected_services'] as $sid) {
                $stmtService->execute([$booking_id, $sid]);
            }

            // Insert customer inclusions with schedule
            if (!empty($_SESSION['selected_inclusions'])) {
                $stmtInclusion = $conn->prepare("
                    INSERT INTO customer_inclusion_schedule
                    (booking_id, inclusion_id, scheduled_date, scheduled_time, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");

                foreach ($_SESSION['selected_inclusions'] as $serviceId => $incId) {
                    // Verify inclusion exists before inserting
                    $stmtCheck = $conn->prepare("SELECT inclusion_id FROM service_inclusions WHERE inclusion_id = ? LIMIT 1");
                    $stmtCheck->execute([$incId]);
                    if ($stmtCheck->fetchColumn()) {
                        $stmtInclusion->execute([$booking_id, $incId, null, null, '']);
                    }
                }
            }

            // Insert booking catering
            if (!empty($_SESSION['selected_catering'])) {
                $stmtCatering = $conn->prepare("INSERT INTO booking_caterings (booking_id, catering_id) VALUES (?, ?)");
                foreach ($_SESSION['selected_catering'] as $cid) {
                    $stmtCatering->execute([$booking_id, $cid]);
                }
            }

            // Clear sessions
            unset($_SESSION['selected_theme'], $_SESSION['selected_services'], $_SESSION['selected_catering'], $_SESSION['selected_venue'], $_SESSION['selected_inclusions']);

            header("Location: my_bookings.php");
            exit();
        }
    }

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

// Function declared once at the top
function venue_image_src_for_output($path) {
    // Adjust this according to your project folder
    return '/event_mt/php/' . $path;
}
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];

    // Get logged in customer info
    $stmt = $conn->prepare("SELECT user_id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS full_name, email 
                            FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    $services = $conn->query("SELECT * FROM services ORDER BY service_name")->fetchAll(PDO::FETCH_ASSOC);
    $themes = $conn->query("SELECT * FROM themes ORDER BY theme_name")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all venues
    $venues = $conn->query("SELECT * FROM venues ORDER BY venue_id DESC")->fetchAll(PDO::FETCH_ASSOC);


    // Attach images to each venue like themes
    foreach ($venues as &$venue) {
        $stmtImgs = $conn->prepare("SELECT image_path FROM venue_images WHERE venue_id = :vid");
        $stmtImgs->execute([':vid' => $venue['venue_id']]);
        $images = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

        if ($images) {
            foreach ($images as &$img) {
                $img['image_src'] = venue_image_src_for_output($img['image_path']);
            }
            $venue['venue_images'] = $images;
        } else {
            $venue['venue_images'] = [['image_src' => "../img/no-image.jpg"]];
        }
    }

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../bootstrap/bootstrap.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../bootstrap/bootstrap.css"> -->
    <style>
 /* General body */
    
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f8f8;
    color: #333;
    margin: 0;
    padding: 0;
    margin-left: 260px; /* sidebar */
    font-size: 16;
}


.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 70px;
    left: 0;
    background: #fff;
    color: #333;
    padding-top: 30px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

.sidebar h2 {
    padding-left: 20px;
    margin-bottom: 20px;
    font-family: cursive;
    color: #dcb6a3;
    font-size: 28px;
}

.sidebar-nav {
    list-style: none;
    padding: 0 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-btn {
    display: block;
    width: 100%;
    padding: 12px 20px;
    text-decoration: none;
    color: #333;
    background-color: #f4f4f4;
    border-radius: 8px;
    font-weight: 500;
    transition: 0.3s;
    text-align: left;
}

.sidebar-btn:hover {
    background-color: #dcb6a3;
    color: #fff;
}

.sidebar-btn.active {
    background-color: #dcb6a3;
    color: #fff;
    font-weight: 600;
}

.sidebar-btn.logout-btn {
    background-color: #f4f4f4;
    color: #333;
}

.sidebar-btn.logout-btn:hover {
    background-color: #f56565;
}


/* Cards */
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    font-weight: bold;
    color: #dcb6a3;
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
}

.card-body {
    color: #333;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}

table th {
    background: #f0f0f0;
    color: #333;
    padding: 12px;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

table tr:hover {
    background: #f9f9f9;
}

/* Theme Cards */
.theme-card {
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    transition: transform 0.3s, box-shadow 0.3s;
}

.theme-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.theme-card .carousel-inner img {
    border-radius: 10px 10px 0 0;
    height: 220px;
    object-fit: cover;
}

/* Buttons */
.btn-primary {
    background-color: #dcb6a3;
    color: #fff;
    border: none;
    border-radius: 20px;
    transition: background 0.3s;
}

.btn-primary:hover {
    background-color: #c49b88;
}

/* Notifications List */
.card ul {
    list-style-type: disc;
    padding-left: 20px;
}

.card ul li {
    margin-bottom: 10px;
}

.dashboard-header {
    position: fixed;
    top: 0;
    left: 0px; /* space for sidebar */
    right: 0;
    height: 90px;
    background: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 10;
}

.dashboard-header .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.dashboard-header .logo-img {
    width: 100px;
    height: auto;
}

.dashboard-header nav ul {
    display: flex;
    list-style: none;
    gap: 20px;
}

.dashboard-header nav ul li a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: 0.3s;
}

.dashboard-header nav ul li a.active,
.dashboard-header nav ul li a:hover {
    color: #dcb6a3;
    font-weight: 700;
}

.header-profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
}

.profile-link {
    text-decoration: none;
    color: #333;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background-color: #dcb6a3;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    color: white;
    font-size: 18px;
    transition: 0.3s;
}

.profile-circle:hover {
    background-color: #c49b88;
}

.profile-name {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    max-width: 80px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


/* Push dashboard content down below header */
.container {
    margin-top: 100px; /* adjust for header height */
}



</style>
</head>
<body>
    <header class="dashboard-header">
        <div class="logo">
            <img src="../img/logo.png" alt="EventLink" class="logo-img">
            Event<span style="font-family: 'Dancing Script', cursive; font-size:40px; color: #dcb6a3;">Link</span>
        </div>
        <div class="header-profile">
            <a href="profile.php" class="profile-link">
                <div class="profile-circle">
                    <?= strtoupper(substr($customer['full_name'], 0, 1)) ?>
                </div>
                <div class="profile-name"><?= htmlspecialchars($customer['full_name']) ?></div>
            </a>
        </div>

    </header>

  <!-- Header Navigation -->
    <div class="sidebar">
        <h2>Menu</h2>
        <ul class="sidebar-nav">
            <li><a href="customer_dashboard.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'customer_dashboard.php' ? 'active' : '' ?>">🏠Dashboard</a></li>
            <li><a href="my_bookings.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'my_bookings.php' ? 'active' : '' ?>">📅My Bookings</a></li>
            <li><a href="book_service.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'book_service.php' ? 'active' : '' ?>">🛎️Book Services</a></li>
            <li><a href="catering.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'catering.php' ? 'active' : '' ?>">📅Catering Services</a></li>
            <li><a href="all_venues.php?from_sidebar=1" class="sidebar-btn<?= basename($_SERVER['PHP_SELF']) == 'catering.php' ? 'active' : '' ?>">🏢Venues</a></li>
            <li><a href="payments.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>">💳Payments</a></li>
            <li><a href="notifications.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : '' ?>">🔔Notifications</a></li>
            <li><a href="logout.php" class="sidebar-btn logout-btn">🚪Logout</a></li>
        </ul>
    </div>
      
        <div class="container">
            <h2 class="mb-4 text-start" style="font-weight: bold;color: #dcb6a3;background: #fff;border-bottom: 1px solid #e5e5e5;" >🏷️Available Themes</h2>
            <div class="row">
                <?php if ($themes): ?>
                    <?php foreach ($themes as $theme): ?>
                        <?php
                        $stmt = $conn->prepare("SELECT image_path FROM theme_images WHERE theme_id = ?");
                        $stmt->execute([$theme['theme_id']]);
                        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <div class="col-md-5 mb-4">
                            <div class="card theme-card shadow-sm">
                                <?php if ($images): ?>
                                    <div id="carousel<?= $theme['theme_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                        <div class="carousel-inner">
                                            <?php foreach ($images as $index => $img): ?>
                                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                    <img src="../uploads/<?= htmlspecialchars($img['image_path']) ?>" class="d-block w-100" alt="Theme Image">
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php if (count($images) > 1): ?>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $theme['theme_id'] ?>" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Previous</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $theme['theme_id'] ?>" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Next</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <img src="../img/no-image.jpg" class="d-block w-100" style="height:220px; object-fit:cover;">
                                <?php endif; ?>

                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($theme['theme_name']) ?></h5>
                                    <p class="card-text text-muted"><?= htmlspecialchars($theme['category']) ?></p>
                                    <p class="card-text"><?= htmlspecialchars($theme['description']) ?></p>
                                    <p class="price-tag">₱<?= number_format($theme['price'], 2) ?></p>
                                    <form action="book_service.php" method="POST">
                                        <input type="hidden" name="theme_id" value="<?= $theme['theme_id'] ?>">
                                        <button type="submit" class="btn btn-primary w-100">Select Theme</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No themes available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
<?php
$stmtVenues = $conn->query("SELECT * FROM venues ORDER BY venue_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4 text-start" style="font-weight: bold;color: #dcb6a3;background: #fff;border-bottom: 1px solid #e5e5e5;">
        🏷️Available Venues
    </h2>
    <div class="venue-list" style="display:flex; flex-wrap:wrap; gap:20px;">

        <?php 
        $maxVenues = 6;
        $count = 0;
        foreach($venues as $v): 
            if ($count >= $maxVenues) break;
            $count++;

            // Fetch images for this venue
            $stmt = $conn->prepare("SELECT * FROM venue_images WHERE venue_id=:vid ORDER BY id ASC");
            $stmt->execute([':vid'=>$v['venue_id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        
        <div class="venue-card" style="width:100%; max-width:400px; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,.06); background:#fff;">
            <div id="carousel<?= $v['venue_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php if(!empty($images)): foreach($images as $index => $img): ?>
                        <div class="carousel-item <?= $index===0?'active':'' ?>">
                            <img src="<?= htmlspecialchars($img['image_path']) ?>" class="d-block w-100" alt="Venue Image">
                        </div>
                    <?php endforeach; else: ?>
                        <div class="carousel-item active">
                            <img src="" class="d-block w-100" style="height:220px; object-fit:cover;" alt="No Image Available">
                        </div>
                    <?php endif; ?>
                </div>

                <?php if(count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $v['venue_id'] ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $v['venue_id'] ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                <?php endif; ?>
            </div>

            <div class="card-body" style="padding:15px;">
                <h3><?= htmlspecialchars($v['venue_name']) ?></h3>
                <p><strong>Location:</strong> <?= htmlspecialchars($v['location'] ?? '-') ?></p>
                <p><strong>Capacity:</strong> <?= $v['capacity'] ?? 0 ?></p>
                <p><strong>Tables:</strong> <?= $v['tables'] ?? 0 ?></p>
                <p><strong>Chairs:</strong> <?= $v['chairs'] ?? 0 ?></p>
                <p><strong>Others:</strong> <?= htmlspecialchars($v['others'] ?? '-') ?></p>
                <p><strong>Price:</strong> <?= $v['price'] !== null ? '₱' . number_format($v['price'],2) : '-' ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

   <?php if (count($venues) > $maxVenues): ?>
    <div class="text-center mt-3">
        <a href="all_venues.php?from_sidebar=1" class="btn btn-primary">
            View All Venues
        </a>
    </div>
<?php endif; ?>

</div>

</div>



        <!-- Services -->
        <div class="card">
            <div class="card-header">📋 Available Services</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <!-- <th>Price</th> -->
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($services as $svc): ?>
                        <tr>
                            <td><?= htmlspecialchars($svc['service_name']) ?></td>
                            <td><?= htmlspecialchars($svc['description']) ?></td>
                            <!-- <td>₱<?= number_format($svc['price'],2) ?></td> -->
                            <td><a href="book_service.php?service_id=<?= $svc['service_id'] ?>" class="btn btn-primary btn-sm">Book</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">📅 My Bookings</div>
            <div class="card-body">
                <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Service Name</th>
                        <th>Venue</th>
                        <th>Status</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($bookings)): ?>
                        <?php foreach($bookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['event_name']) ?></td>
                                <td><?= htmlspecialchars($b['event_date']) ?></td>
                                <td><?= htmlspecialchars($b['event_time']) ?></td>
                                <td><?= htmlspecialchars($b['services']) ?></td>
                                <!-- <td><?= htmlspecialchars($b['staff_name']) ?></td> -->
                                <td><?= htmlspecialchars($b['venue_name']) ?></td>
                                <td><?= htmlspecialchars($b['status']) ?></td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">You have no bookings yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
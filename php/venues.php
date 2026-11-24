<?php
// admin/venues.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Admin access check ---
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- DB Connection ---
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// --- Helper function ---
function safe($v) {
    return $v === null || $v === '' ? '-' : htmlspecialchars($v);
}

// --- Image Upload Function ---
function uploadVenueImages($venue_id, $files, $conn) {
    if (!empty($files['name'][0])) {
        $targetDir = __DIR__ . "/uploads/venues/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        foreach ($files['name'] as $index => $filename) {
            $tmpName = $files['tmp_name'][$index];
            $safeName = time() . "_" . preg_replace('/[^A-Za-z0-9\-_\.]/', '_', basename($filename));
            $dest = $targetDir . $safeName;
            if (move_uploaded_file($tmpName, $dest)) {
                $conn->prepare("
                    INSERT INTO venue_images (venue_id, image_path, created_at)
                    VALUES (:venue_id, :image_path, NOW())
                ")->execute([
                    ':venue_id' => $venue_id,
                    ':image_path' => "uploads/venues/" . $safeName
                ]);
            }
        }
    }
}

// --- Handle Add Venue ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = trim($_POST['venue_name'] ?? '');
    $location = trim($_POST['location'] ?? null);
    $capacity = $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null;
    $tables = $_POST['tables'] !== '' ? (int)$_POST['tables'] : 0;
    $chairs = $_POST['chairs'] !== '' ? (int)$_POST['chairs'] : 0;
    // $others = trim($_POST['others'] ?? null);
    $price = $_POST['price'] !== '' ? (float)$_POST['price'] : null;

    $stmt = $conn->prepare("
        INSERT INTO venues 
        (venue_name, location, capacity, tables, chairs, price, created_at)
        VALUES (:name, :location, :capacity, :tables, :chairs, :price, NOW())
    ");
    $stmt->execute([
        ':name' => $name,
        ':location' => $location ?: null,
        ':capacity' => $capacity,
        ':tables' => $tables,
        ':chairs' => $chairs,
        // ':others' => $others,
        ':price' => $price
    ]);
    $venue_id = $conn->lastInsertId();
    uploadVenueImages($venue_id, $_FILES['venue_images'], $conn);

    $_SESSION['flash_success'] = "Venue added successfully.";
    header("Location: venues.php");
    exit();
}

// --- Handle Edit Venue ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = (int)($_POST['venue_id'] ?? 0);
    $name = trim($_POST['venue_name'] ?? '');
    $location = trim($_POST['location'] ?? null);
    $capacity = $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null;
    $tables = $_POST['tables'] !== '' ? (int)$_POST['tables'] : 0;
    $chairs = $_POST['chairs'] !== '' ? (int)$_POST['chairs'] : 0;
    // $others = trim($_POST['others'] ?? null);
    $price = $_POST['price'] !== '' ? (float)$_POST['price'] : null;

    $stmt = $conn->prepare("
        UPDATE venues
        SET venue_name=:name, location=:location, capacity=:capacity, tables=:tables, chairs=:chairs, price=:price
        WHERE venue_id=:id
    ");
    $stmt->execute([
        ':name' => $name,
        ':location' => $location ?: null,
        ':capacity' => $capacity,
        ':tables' => $tables,
        ':chairs' => $chairs,
        // ':others' => $others,
        ':price' => $price,
        ':id' => $id
    ]);
    uploadVenueImages($id, $_FILES['venue_images'], $conn);

    $_SESSION['flash_success'] = "Venue updated successfully.";
    header("Location: venues.php");
    exit();
}

// --- Handle Delete Venue ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT image_path FROM venue_images WHERE venue_id=:id");
    $stmt->execute([':id' => $id]);
    $imgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($imgs as $img) {
        if (file_exists(__DIR__ . '/' . $img['image_path'])) @unlink(__DIR__ . '/' . $img['image_path']);
    }
    $conn->prepare("DELETE FROM venue_images WHERE venue_id=:id")->execute([':id' => $id]);
    $conn->prepare("DELETE FROM venues WHERE venue_id=:id")->execute([':id' => $id]);

    $_SESSION['flash_success'] = "Venue deleted successfully.";
    header("Location: venues.php");
    exit();
}

// --- Fetch venues ---
$venues = $conn->query("SELECT * FROM venues ORDER BY venue_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- Flash messages ---
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin - Manage Venues</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link rel="stylesheet" href="../fontawesome/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">

<style>

body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    margin-left: 260px;
    background: #f0f2f5;
    color: #333;
    
}

.dashboard-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 90px;
    background: #ffffff; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    z-index: 10;
}

.dashboard-header .logo-img {
    width: 100px;
}

.header-profile {
    font-weight: 600;
    color: #555;
}

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

.sidebar h2 {
    padding-left: 20px;
    color: #ffb74d; 
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
    padding: 12px 20px;
    text-decoration: none;
    color: #e0e0e0;
    background: #3b4a5c; 
    border-radius: 8px;
    transition: .3s;
}

.sidebar-btn:hover {
    background: #ffb74d;
    color: #1a1a1a;
}

.sidebar-btn.active {
    background: #ffb74d;
    color: #1a1a1a;
    font-weight: 600;
}
.venue-list { display:flex; flex-wrap:wrap; gap:20px; }
.venue-card { width:100%; max-width:400px; border-radius:12px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,.06); background:#fff; }
.card-body { padding:15px; }
.card-body h3 { margin:0 0 8px; }
.card-body p { margin:6px 0; color:#555; }
.actions { display:flex; gap:8px; margin-top:10px; }
</style>
</head>
<body>

<!-- HEADER (exact same) -->
<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" class="logo-img">
        Event<span style="font-family:'Dancing Script',cursive;color:#dcb6a3; font-size:40px;"> Link</span>
    </div>

    <div class="header-profile">
        Admin
    </div>
</header>

<!-- SIDEBAR (exact same design) -->
<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php" class="sidebar-btn"> Dashboard</a></li>
        <a href="themes.php" class="sidebar-btn">Manage Themes</a>
        <a href="manage_catering.php" class="sidebar-btn"> Manage Catering</a>
        <a href="services.php" class="sidebar-btn">Manage Services</a>
        <a href="venues.php" class="sidebar-btn"  active >Manage Venues</a>
        <a href="customers.php" class="sidebar-btn">Customers</a>
        <a href="events.php" class="sidebar-btn">Events</a>
        <a href="bookings.php" class="sidebar-btn">Bookings</a>
        <a href="admin_notifications.php" class="sidebar-btn"> Notifications</a>
        <li><a href="logout.php" class="sidebar-btn">🚪 Logout</a></li>
    </ul>
</div>

<div class="container-fluid" style="margin-top:10%; margin-left:20px;">
<h1>Manage Venues</h1>
<?php if($flash_success): ?><div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div><?php endif; ?>
<?php if($flash_error): ?><div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div><?php endif; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa fa-plus"></i> Add Venue</button>

<div class="venue-list">
<?php foreach($venues as $v):
    $stmt = $conn->prepare("SELECT * FROM venue_images WHERE venue_id=:vid ORDER BY id ASC");
    $stmt->execute([':vid'=>$v['venue_id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="venue-card">
    <div id="carousel<?= $v['venue_id'] ?>" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
        <?php if(!empty($images)): foreach($images as $index => $img): ?>
            <div class="carousel-item <?= $index===0?'active':'' ?>">
                <img src="<?= htmlspecialchars($img['image_path']) ?>" class="d-block w-100" alt="Venue Image">
            </div>
        <?php endforeach; else: ?>
            <div class="carousel-item active">
                <img src="../img/venue_placeholder.png" class="d-block w-100" alt="Placeholder">
            </div>
        <?php endif; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $v['venue_id'] ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $v['venue_id'] ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <div class="card-body">
        <h3><?= safe($v['venue_name']) ?></h3>
        <p><strong>Location:</strong> <?= safe($v['location']) ?></p>
        <p><strong>Capacity:</strong> <?= $v['capacity'] ?? 0 ?></p>
        <p><strong>Tables:</strong> <?= $v['tables'] ?? 0 ?></p>
        <p><strong>Chairs:</strong> <?= $v['chairs'] ?? 0 ?></p>
        <!-- <p><strong>Others:</strong> <?= safe($v['others']) ?></p> -->
        <p><strong>Price:</strong> <?= $v['price']!==null?'₱'.number_format($v['price'],2):'-' ?></p>
        <div class="actions">
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $v['venue_id'] ?>"><i class="fa fa-pen"></i> Edit</button>
            <a href="venues.php?delete=<?= $v['venue_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this venue?')"><i class="fa fa-trash"></i> Delete</a>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal<?= $v['venue_id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="venue_id" value="<?= $v['venue_id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title">Edit Venue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label>Venue Name</label><input class="form-control" name="venue_name" required value="<?= htmlspecialchars($v['venue_name']) ?>"></div>
          <div class="col-md-6"><label>Location</label><input class="form-control" name="location" value="<?= htmlspecialchars($v['location']??'') ?>"></div>
          <div class="col-md-4"><label>Capacity</label><input class="form-control" type="number" name="capacity" value="<?= htmlspecialchars($v['capacity']??'') ?>"></div>
          <div class="col-md-4"><label>Tables</label><input class="form-control" type="number" name="tables" value="<?= htmlspecialchars($v['tables']??0) ?>"></div>
          <div class="col-md-4"><label>Chairs</label><input class="form-control" type="number" name="chairs" value="<?= htmlspecialchars($v['chairs']??0) ?>"></div>
          <!-- <div class="col-md-12"><label>Others</label><input class="form-control" type="text" name="others" value="<?= htmlspecialchars($v['others']??'') ?>"></div> -->
          <div class="col-md-4"><label>Price (₱)</label><input class="form-control" type="number" step="0.01" name="price" value="<?= htmlspecialchars($v['price']??'') ?>"></div>
          <div class="col-md-12"><label>New Images (optional)</label><input class="form-control" type="file" name="venue_images[]" multiple accept="image/*"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Save changes</button>
      </div>
    </form>
  </div>
</div>

<?php endforeach; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="action" value="add">
      <div class="modal-header">
        <h5 class="modal-title">Add Venue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label>Venue Name</label><input class="form-control" name="venue_name" required></div>
          <div class="col-md-6"><label>Location</label><input class="form-control" name="location"></div>
          <div class="col-md-4"><label>Capacity</label><input class="form-control" type="number" name="capacity"></div>
          <div class="col-md-4"><label>Tables</label><input class="form-control" type="number" name="tables"></div>
          <div class="col-md-4"><label>Chairs</label><input class="form-control" type="number" name="chairs"></div>
          <!-- <div class="col-md-12"><label>Others</label><input class="form-control" type="text" name="others"></div> -->
          <div class="col-md-4"><label>Price (₱)</label><input class="form-control" type="number" step="0.01" name="price"></div>
          <div class="col-md-12"><label>Images (optional)</label><input class="form-control" type="file" name="venue_images[]" multiple accept="image/*"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Venue</button>
      </div>
    </form>
  </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT CONCAT(first_name,' ',COALESCE(middle_name,''),' ',last_name) AS full_name FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    $menu_categories = ['Appetizer','Main Course','Side Dish','Dessert','Beverages'];

    // Handle form submission
    if(isset($_POST['add_menu'])){
        $menu_name = $_POST['menu_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $meal_type = $_POST['meal_type'];

        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "images/".$image);

        $stmt = $conn->prepare("INSERT INTO catering_menus (menu_name, description, price, image, meal_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$menu_name, $description, $price, $image, $meal_type]);
        header("Location: catering_admin.php");
    }

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🍽️ Add Menu | Admin</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
<style>
/* === SAME DESIGN AS CATERING.PHP === */
body { font-family: 'Poppins', sans-serif; background-color:#f8f8f8; margin:0; padding:0; margin-left:260px; }
.sidebar { width:260px; height:100vh; position:fixed; top:70px; left:0; background:#fff; color:#333; padding-top:30px; box-shadow:2px 0 5px rgba(0,0,0,.1); }
.sidebar h2 { padding-left:20px; margin-bottom:20px; font-family:cursive; color:#dcb6a3; font-size:28px; }
.sidebar-nav { list-style:none; padding:0 10px; display:flex; flex-direction:column; gap:10px; }
.sidebar-btn { display:block; width:100%; padding:12px 20px; text-decoration:none; color:#333; background:#f4f4f4; border-radius:8px; font-weight:500; transition:.3s; text-align:left; }
.sidebar-btn:hover { background:#dcb6a3; color:#fff; }
.sidebar-btn.active { background:#dcb6a3; color:white; }
.sidebar-btn.logout-btn { background:#f4f4f4; color:#333; }
.sidebar-btn.logout-btn:hover { background:#f56565; }

.dashboard-header { position:fixed; top:0; left:0; right:0; height:90px; background:#fff; display:flex; justify-content:space-between; align-items:center; padding:0 30px; box-shadow:0 2px 8px rgba(0,0,0,.1); z-index:10; }
.dashboard-header .logo-img { width:100px; }
.header-profile { display:flex; flex-direction:column; align-items:center; gap:5px; }
.profile-link { text-decoration:none; color:#333; display:flex; flex-direction:column; align-items:center; }
.profile-circle { width:45px; height:45px; border-radius:50%; background:#dcb6a3; display:flex; justify-content:center; align-items:center; font-weight:bold; color:white; font-size:18px; }
.profile-name { font-size:12px; font-weight:500; text-align:center; max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

.container { margin-top:120px; padding:20px; }
form { background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 18px rgba(0,0,0,.08); max-width:600px; margin:auto; }
form label { font-weight:500; }
form input, form textarea, form select { width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ccc; }
form button { padding:10px; background-color:#dcb6a3; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; transition:.3s; }
form button:hover { background-color:#c49b88; }
</style>
</head>
<body>

<!-- HEADER -->
<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" class="logo-img" alt="EventLink">
        Event<span style="font-family: 'Dancing Script', cursive; font-size:40px; color:#dcb6a3;">Link</span>
    </div>
    <div class="header-profile">
        <a href="profile.php" class="profile-link">
            <div class="profile-circle"><?= strtoupper(substr($admin['full_name'],0,1)) ?></div>
            <div class="profile-name"><?= htmlspecialchars($admin['full_name']) ?></div>
        </a>
    </div>
</header>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Admin</h2>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='admin_dashboard.php'?'active':'' ?>">🏠Dashboard</a></li>
        <li><a href="catering_admin.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='catering_admin.php'?'active':'' ?>">🍽️ Catering</a></li>
        <li><a href="bookings_admin.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='bookings_admin.php'?'active':'' ?>">📅Bookings</a></li>
        <li><a href="logout.php" class="sidebar-btn logout-btn">🚪Logout</a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="container">
    <h1>🍽️ Add New Menu</h1>
    <form method="POST" enctype="multipart/form-data">
        <label>Menu Name</label>
        <input type="text" name="menu_name" required>
        <label>Description</label>
        <textarea name="description" required></textarea>
        <label>Price</label>
        <input type="number" step="0.01" name="price" required>
        <label>Meal Type</label>
        <select name="meal_type" required>
            <?php foreach($menu_categories as $cat): ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <label>Image</label>
        <input type="file" name="image" required>
        <button type="submit" name="add_menu">Add Menu</button>
    </form>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

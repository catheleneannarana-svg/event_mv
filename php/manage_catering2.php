<?php
session_start();

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', COALESCE(middle_name,''), ' ', last_name) AS full_name FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle Add Menu
    if (isset($_POST['add_menu'])) {
        $menu_name = $_POST['menu_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $meal_type = $_POST['meal_type'];

        $image = $_FILES['image']['name'];
        $tmp_name = $_FILES['image']['tmp_name'];
        $upload_dir = "../uploads/";
        move_uploaded_file($tmp_name, $upload_dir . $image);

        $stmt = $conn->prepare("INSERT INTO catering_menus (menu_name, description, price, image, meal_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$menu_name, $description, $price, $image, $meal_type]);

        header("Location: manage_catering.php");
        exit();
    }

    // Fetch menus
    $stmt = $conn->query("SELECT * FROM catering_menus ORDER BY meal_type, menu_name");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$menu_categories = ['Appetizer', 'Main Course', 'Side Dish', 'Dessert', 'Beverages'];
$menu_by_category = [];
foreach ($menu_categories as $cat) { $menu_by_category[$cat] = []; }
foreach ($menus as $menu) {
    $type = $menu['meal_type'] === 'Salad' ? 'Appetizer' : $menu['meal_type'];
    if (isset($menu_by_category[$type])) { 
        $menu_by_category[$type][] = $menu; 
    }
}

// Current page for sidebar highlight
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Catering | EventLink</title>
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


/* ---------------- MAIN CONTAINER ---------------- */
.container{ margin-top:120px; padding:20px; }

/* ---------------- CATEGORY TABS ---------------- */
.nav-tabs{
    border-bottom:none; display:flex; gap:20px; margin-bottom:15px;
}
.nav-tabs .nav-link{
    border:none; border-radius:0; color:#333; font-weight:500; transition:.2s;
}
.nav-tabs .nav-link:hover{
    background: rgba(255,183,77,0.1); color:#ffb74d; border:2px solid #ffb74d; border-bottom:none; border-radius:4px 4px 0 0;
}
.nav-tabs .nav-link.active{
    background: rgba(255,183,77,0.15); color:#ffb74d; border:2px solid #ffb74d; border-bottom:none; font-weight:600; border-radius:4px 4px 0 0;
}

/* ---------------- MENU CARDS ---------------- */
.menu-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:25px;}
.menu-card{
    background:#fff; border-radius:12px; padding:20px; 
    box-shadow:0 4px 12px rgba(0,0,0,.08); text-align:center; display:flex; flex-direction:column;
}
.menu-card img{ width:100%; height:150px; object-fit:cover; border-radius:10px; margin-bottom:10px; }
.menu-card h6{ margin:5px 0; font-size:16px; font-weight:600; }
.menu-card p{ margin:2px 0; font-size:14px; }
.menu-card p.price{ font-weight:bold; color:#dcb6a3; }
.book-btn{ margin-top:auto; padding:10px; background:#dcb6a3; color:#fff; border:none; border-radius:8px; cursor:pointer;}
.book-btn:hover{ background:#c49b88; }

/* ---------------- ADD MENU MODAL ---------------- */
#addMenuModal{
    position:fixed; top:0; left:260px; width:calc(100% - 260px); height:100%; 
    background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; z-index:20; display:none;
}
.menu-form{
    background:#fff; padding:25px; border-radius:12px; width:500px; max-width:90%;
    box-shadow:0 4px 18px rgba(0,0,0,.08);
}
.menu-form h2{margin-bottom:15px;}
.menu-form input, .menu-form textarea, .menu-form select{width:100%; padding:10px; margin-bottom:12px; border-radius:8px; border:1px solid #ccc;}
.menu-form button{padding:10px;border:none;border-radius:8px;cursor:pointer;font-weight:600;margin-right:10px;}
.menu-form button[type="submit"]{ background:#dcb6a3; color:#fff;}
.menu-form button[type="submit"]:hover{ background:#c49b88; }
.menu-form button.cancel{ background:#f56565; color:#fff; }

.logo{ font-size:30px; font-weight:700; }
</style>
</head>
<body>

<header class="dashboard-header">
    <div class="logo"><img src="../img/logo.png" class="logo-img"> Event<span> Link</span></div>
    <div class="header-profile"><?= htmlspecialchars($admin['full_name']) ?></div>
</header>

<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php" class="sidebar-btn active"> Dashboard</a></li>
        <a href="themes.php" class="sidebar-btn">Manage Themes</a>
        <a href="manage_catering.php" class="sidebar-btn"> Manage Catering</a>
        <a href="services.php" class="sidebar-btn">Manage Services</a>
        <a href="venues.php" class="sidebar-btn">Manage venues</a>
        <a href="customers.php" class="sidebar-btn">Customers</a>
        <a href="events.php" class="sidebar-btn">Events</a>
        <a href="bookings.php" class="sidebar-btn">Bookings</a>
        <a href="admin_notifications.php" class="sidebar-btn"> Notifications</a>
        <li><a href="logout.php" class="sidebar-btn">🚪 Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ul class="nav nav-tabs mb-0">
            <li class="nav-item"><a class="nav-link <?= !isset($_GET['category']) ? 'active' : '' ?>" href="manage_catering.php">All</a></li>
            <?php foreach ($menu_categories as $cat): ?>
            <li class="nav-item">
                <a class="nav-link <?= (isset($_GET['category']) && $_GET['category']==$cat) ? 'active' : '' ?>" href="manage_catering.php?category=<?= $cat ?>"><?= $cat ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <button id="addMenuBtn" class="btn book-btn">➕ Add Menu</button>
    </div>

    <?php foreach ($menu_by_category as $category => $items):
        if (isset($_GET['category']) && $_GET['category'] != $category) continue;
    ?>
    <div class="card mb-3 p-3">
        <h4><?= htmlspecialchars($category) ?></h4>
        <div class="menu-grid">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $menu): ?>
                <div class="menu-card">
                    <img src="../uploads/<?= htmlspecialchars($menu['image']) ?>" alt="<?= htmlspecialchars($menu['menu_name']) ?>">
                    <h6><?= htmlspecialchars($menu['menu_name']) ?></h6>
                    <p><?= htmlspecialchars($menu['description']) ?></p>
                    <p class="price">₱<?= number_format($menu['price'],2) ?></p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No available <?= htmlspecialchars($category) ?>.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Menu Modal -->
<div id="addMenuModal">
    <form class="menu-form" method="POST" enctype="multipart/form-data">
        <h2>Add New Menu</h2>
        <label>Menu Name</label><input type="text" name="menu_name" required>
        <label>Description</label><textarea name="description" required></textarea>
        <label>Price</label><input type="number" step="0.01" name="price" required>
        <label>Meal Type</label>
        <select name="meal_type" required>
            <?php foreach ($menu_categories as $cat): ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
            <?php endforeach; ?>
        </select>
        <label>Image</label><input type="file" name="image" required>
        <div style="margin-top:10px;">
            <button type="submit" name="add_menu">Add Menu</button>
            <button type="button" class="cancel" id="closeFormBtn">Cancel</button>
        </div>
    </form>
</div>

<script>
const addBtn = document.getElementById('addMenuBtn');
const modal = document.getElementById('addMenuModal');
const closeBtn = document.getElementById('closeFormBtn');
addBtn.addEventListener('click',()=>modal.style.display='flex');
closeBtn.addEventListener('click',()=>modal.style.display='none');
</script>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

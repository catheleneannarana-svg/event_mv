<?php
require 'add_theme.php';

// Get current page name for sidebar highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Themes | EventLink</title>
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
    font-weight: 600;
}

.sidebar-btn.active {
    background: #ffb74d;
    color: #1a1a1a;
    font-weight: 600;
}


/* ---------------- MAIN CONTAINER ---------------- */
.container { margin-top: 120px; padding: 20px; }

/* ---------------- NAV TABS ---------------- */
.nav-tabs{
    border-bottom: none;
    gap:20px;
    margin-top:0px;
}
.nav-tabs .nav-link {
    border: none;        
    border-radius: 0;    
    color: #333;
    font-weight: 500;
    transition: all 0.2s;
}
.nav-tabs .nav-link:hover {
    background-color: rgba(255, 183, 77, 0.1); 
    color: #ffb74d; 
    border: 2px solid #ffb74d;
    border-bottom: none; 
    border-radius: 4px 4px 0 0;
}
.nav-tabs .nav-link.active {
    background-color: rgba(255, 183, 77, 0.15); 
    color: #ffb74d; 
    border: 2px solid #ffb74d;
    border-bottom: none;
    font-weight: 600;
    border-radius: 4px 4px 0 0;
}

/* ---------------- CARD ---------------- */
.card {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    color: #333;
}

/* ---------------- BUTTONS ---------------- */
.btn-success {
    background-color: #dcb6a3; 
    border-color: #dcb6a3;
    color: white;
    font-weight: 500;
    transition: 0.3s;
}
.btn-success:hover {
    background-color: #c49b88; 
    border-color: #c49b88;
    color: white;
}

.logo{
    font-size: 30px;
    font-weight: 700;
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" class="logo-img">
        Event<span style="font-family:'Dancing Script',cursive;color:#dcb6a3; font-size:40px;"> Link</span>
    </div>
    <div class="header-profile">Admin</div>
</header>

<!-- SIDEBAR -->
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

<!-- MAIN CONTENT -->
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <ul class="nav nav-tabs mb-0">
            <li class="nav-item"><a class="nav-link <?= !isset($_GET['category']) ? 'active' : '' ?>" href="themes.php">All</a></li>
            <li class="nav-item"><a class="nav-link <?= (isset($_GET['category']) && $_GET['category']=='Wedding') ? 'active' : '' ?>" href="themes.php?category=Wedding">Wedding</a></li>
            <li class="nav-item"><a class="nav-link <?= (isset($_GET['category']) && $_GET['category']=='Birthday') ? 'active' : '' ?>" href="themes.php?category=Birthday">Birthday</a></li>
            <li class="nav-item"><a class="nav-link <?= (isset($_GET['category']) && $_GET['category']=='Corporate') ? 'active' : '' ?>" href="themes.php?category=Corporate">Corporate</a></li>
        </ul>

        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addThemeModal">
            ➕ Add Theme
        </button>
    </div>

    <?php foreach ($data as $theme_id => $themeImages): 
        $theme = $themeImages[0];
        if (isset($_GET['category']) && $_GET['category'] != $theme['category']) continue;
    ?>
    <div class="card">
        <h4><?= htmlspecialchars($theme['theme_name']) ?> (<?= htmlspecialchars($theme['category']) ?>)</h4>
        <p><?= htmlspecialchars($theme['description']) ?> | ₱<?= htmlspecialchars($theme['price']) ?></p>
        <div style="display:flex; flex-wrap:wrap;">
            <?php foreach ($themeImages as $img): 
                if (!empty($img['image_path'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($img['image_path']) ?>" width="120" style="margin:5px; border-radius:5px;">
            <?php endif; endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- ADD THEME MODAL -->
    <div class="modal fade" id="addThemeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="add_theme.php" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Theme</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Theme Name</label>
                            <input type="text" class="form-control" name="theme_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="Wedding">Wedding</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Corporate">Corporate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="price" min="200" placeholder="200" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Images</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Theme</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

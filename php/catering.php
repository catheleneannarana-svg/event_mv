<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $user_id = $_SESSION['user_id'];

    // Customer info
    $stmt = $conn->prepare("SELECT CONCAT(first_name,' ',COALESCE(middle_name,''),' ',last_name) AS full_name FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // All menu items
    $stmt = $conn->query("SELECT * FROM menu_items ORDER BY created_at DESC");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Categorize menus dynamically
    $menu_categories = [];
    foreach ($menus as $menu) {
        $cat = $menu['category'];
        if (!isset($menu_categories[$cat])) $menu_categories[$cat] = [];
        $menu_categories[$cat][] = $menu;
    }

    // Fetch approved catering users
    $stmt = $conn->prepare("SELECT * FROM catering_applications WHERE status = ?");
    $stmt->execute(['Approved']);
    $approved_caterings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Menus grouped by catering user
    $catering_menus = [];
    foreach ($menus as $menu) {
        $cateringId = $menu['catering_user_id'] ?? null;
        if ($cateringId) {
            if (!isset($catering_menus[$cateringId])) $catering_menus[$cateringId] = [];
            $catering_menus[$cateringId][] = $menu;
        }
    }

    // Handle skip catering
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skip_catering'])) {
        unset($_SESSION['selected_catering']); // clear catering session
        header("Location: all_venues.php"); // redirect to venue selection
        exit();
    }

    // Handle confirm selected menus
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menus'])) {
        $_SESSION['selected_catering'] = $_POST['menus'];
        header("Location: all_venues.php"); // redirect to venue selection
        exit();
    }

} catch(PDOException $e) {
    die("Error: ".$e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>🍽️ Catering | Event Management</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body { font-family: 'Poppins', sans-serif; background-color: #f8f8f8; color: #333; margin:0; padding:0; margin-left:260px;}
.sidebar { width:260px; height:100vh; position:fixed; top:70px; left:0; background:#fff; padding-top:30px; box-shadow:2px 0 5px rgba(0,0,0,0.1);}
.sidebar h2 { padding-left:20px; margin-bottom:20px; font-family:cursive; color:#dcb6a3; font-size:28px;}
.sidebar-nav { list-style:none; padding:0 10px; display:flex; flex-direction:column; gap:10px;}
.sidebar-btn { display:block; width:100%; padding:12px 20px; text-decoration:none; color:#333; background:#f4f4f4; border-radius:8px; font-weight:500; transition:.3s;}
.sidebar-btn:hover, .sidebar-btn.active { background-color:#dcb6a3; color:#fff;}
.dashboard-header { position:fixed; top:0; left:0; right:0; height:90px; background:#fff; display:flex; justify-content:space-between; align-items:center; padding:0 30px; box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:10;}
.dashboard-header .logo-img { width:100px;}
.dashboard-header .logo { display:flex; align-items:center; gap:10px; font-size:28px; font-weight:700; color:#333;}
.header-profile { display:flex; flex-direction:column; align-items:center; gap:5px;}
.profile-link { text-decoration:none; color:#333; display:flex; flex-direction:column; align-items:center;}
.profile-circle { width:45px; height:45px; border-radius:50%; background-color:#dcb6a3; display:flex; justify-content:center; align-items:center; font-weight:bold; color:white; font-size:18px; transition:.3s;}
.profile-circle:hover { background-color:#c49b88;}
.profile-name { font-size:12px; font-weight:500; text-align:center; max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;}
.container { margin-top:110px; padding:20px;}
.tab-container { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px;}
.main-tab-btn { padding:10px 25px; background:#f4f4f4; border:none; border-radius:8px; cursor:pointer; font-weight:600; transition:.3s; box-shadow:0 2px 6px rgba(0,0,0,0.08);}
.main-tab-btn:hover { background-color:#e4d1c7;}
.main-tab-btn.active { background-color:#dcb6a3; color:white;}
.category-tab-btn { padding:8px 18px; background:#fff; border:2px solid #dcb6a3; border-radius:8px; cursor:pointer; font-weight:500; transition:.3s;}
.category-tab-btn:hover { background-color:#f6e9e3;}
.category-tab-btn.active { background-color:#dcb6a3; color:white; border-color:#dcb6a3;}
.tab-content, .menu-type-content { display:none;}
.tab-content.active, .menu-type-content.active { display:block;}
.menu-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:25px;}
.menu-card { background:#fff; border-radius:12px; padding:15px; box-shadow:0 4px 18px rgba(0,0,0,0.08); display:flex; flex-direction:column; text-align:center; transition:.3s;}
.menu-card:hover { transform:translateY(-5px);}
.menu-card img { width:100%; height:170px; object-fit:cover; border-radius:10px; margin-bottom:10px;}
.menu-card h3 { margin:8px 0; font-size:18px; font-weight:600;}
.menu-card p { margin:5px 0; font-size:14px;}
.book-btn { margin-top:auto; padding:10px; background-color:#dcb6a3; color:#fff; border:none; border-radius:8px; cursor:pointer; transition:.3s; font-weight:600;}
.book-btn:hover { background-color:#c49b88;}
.book-btn.selected { background-color:#6c4a3a; cursor:default;}
.skip-btn { padding:10px 25px; background:#6c757d; color:#fff; border:none; border-radius:8px; cursor:pointer; font-weight:600; transition:.3s; margin-bottom:20px;}
.skip-btn:hover { background:#5a6268;}
.text-end { display:flex; justify-content:flex-end; margin-bottom:20px;}
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
            <div class="profile-circle"><?= strtoupper(substr($customer['full_name'],0,1)) ?></div>
            <div class="profile-name"><?= htmlspecialchars($customer['full_name']) ?></div>
        </a>
    </div>
</header>

<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="customer_dashboard.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='customer_dashboard.php'?'active':'' ?>">🏠Dashboard</a></li>
        <li><a href="my_bookings.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='my_bookings.php'?'active':'' ?>">📅My Bookings</a></li>
        <li><a href="book_service.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='book_service.php'?'active':'' ?>">🛎️Book Services</a></li>
        <li><a href="catering.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='catering.php'?'active':'' ?>">📅Catering Services</a></li>
        <li><a href="payments.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='payments.php'?'active':'' ?>">💳Payments</a></li>
        <li><a href="notifications.php" class="sidebar-btn <?= basename($_SERVER['PHP_SELF'])=='notifications.php'?'active':'' ?>">🔔Notifications</a></li>
        <li><a href="logout.php" class="sidebar-btn logout-btn">🚪Logout</a></li>
    </ul>
</div>

<div class="container">
<h1>🍽️ Catering Menu</h1>

<!-- Skip Catering Button -->
<div class="text-center mb-4">
    <form method="post" action="catering.php">
        <button type="submit" name="skip_catering" class="skip-btn w-50">Skip / Enter Own Catering</button>
    </form>
</div>

<div class="tab-container">
    <button class="main-tab-btn active" data-tab="our-menu">🍛Menu</button>
    <button class="main-tab-btn" data-tab="restaurants">🍴 Restaurants</button>
</div>

<!-- Our Menu -->
<div class="tab-content active" id="our-menu">
    <div class="tab-container">
        <button class="category-tab-btn active" data-tab="menu-All">All (<?= count($menus) ?>)</button>
        <?php foreach ($menu_categories as $cat=>$items): ?>
            <button class="category-tab-btn" data-tab="menu-<?= $cat ?>"><?= $cat ?> (<?= count($items) ?>)</button>
        <?php endforeach; ?>
    </div>

    <!-- "All" menu tab -->
    <div class="menu-type-content active" id="menu-All">
        <div class="menu-grid">
            <?php foreach ($menus as $menu): ?>
                <div class="menu-card" data-menu-id="<?= $menu['item_id'] ?>">
                    <?php
                    $images = explode(',', $menu['image']);
                    $first = true;
                    ?>
                    <div id="menuCarouselAll<?= $menu['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($images as $img):
                                $img = trim($img);
                                if ($img === '') continue;
                                $imgUrl = "../restaurant/uploads/" . rawurlencode($img);
                            ?>
                            <div class="carousel-item <?= $first ? 'active' : '' ?>">
                                <img src="<?= $imgUrl ?>" class="d-block w-100" alt="<?= htmlspecialchars($menu['name']) ?>">
                            </div>
                            <?php $first = false; endforeach; ?>
                        </div>
                        <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#menuCarouselAll<?= $menu['item_id'] ?>" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#menuCarouselAll<?= $menu['item_id'] ?>" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($menu['name']) ?></h3>
                    <p><?= htmlspecialchars($menu['description']) ?></p>
                    <p>₱<?= number_format($menu['price'], 2) ?></p>
                    <button class="book-btn" <?= strtolower($menu['status']) != 'available' ? 'disabled' : '' ?> >
                        <?= strtolower($menu['status']) == 'available' ? 'Select' : 'Unavailable' ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Category tabs -->
    <?php foreach($menu_categories as $cat => $items): ?>
    <div class="menu-type-content" id="menu-<?= $cat ?>">
        <div class="menu-grid">
            <?php foreach ($items as $menu): ?>
                <div class="menu-card" data-menu-id="<?= $menu['item_id'] ?>">
                    <!-- --- carousel + content same as above --- -->
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Confirm Selected Menus -->
    <div class="text-end">
        <form id="confirmSelectedMenusForm" method="post" action="catering.php">
            <button type="submit" class="book-btn">Confirm Selected Menus</button>
        </form>
    </div>
</div>

<!-- Restaurants Tab -->
<div class="tab-content" id="restaurants">
    <div id="restaurantCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach($approved_caterings as $index => $caterer): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                  <div class="card text-center" style="width: 18rem; margin:auto;">
                        <img src="<?= !empty($caterer['logo']) ? '../uploads/logos/' . rawurlencode(basename($caterer['logo'])) : '../uploads/logos/default.png' ?>" 
                            class="card-img-top" 
                            alt="<?= htmlspecialchars($caterer['catering_name']) ?>" 
                            style="height:200px; object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($caterer['catering_name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($caterer['email']) ?></p>
                            <a href="../caterer_menu.php?id=<?= $caterer['id'] ?>" class="btn btn-primary">
                                View Menu
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#restaurantCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#restaurantCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
<script>
// Tabs
document.querySelectorAll('.main-tab-btn').forEach(tab=>{
    tab.addEventListener('click', ()=>{
        document.querySelectorAll('.main-tab-btn').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
document.querySelectorAll('.category-tab-btn').forEach(tab=>{
    tab.addEventListener('click', ()=>{
        document.querySelectorAll('.category-tab-btn').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.menu-type-content').forEach(c=>c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});

// Menu selection
let selectedMenus = <?php echo json_encode($_SESSION['selected_catering'] ?? []); ?>;

// Mark buttons already selected
selectedMenus.forEach(id=>{
    const btn = document.querySelector(`.menu-card[data-menu-id='${id}'] .book-btn`);
    if(btn){
        btn.classList.add('selected');
        btn.textContent = 'Selected';
    }
});

// Build hidden inputs initially
const form = document.getElementById('confirmSelectedMenusForm');
selectedMenus.forEach(id=>{
    const input = document.createElement('input');
    input.type='hidden';
    input.name='menus[]';
    input.value=id;
    form.appendChild(input);
});

// Handle click selection
document.querySelectorAll('.menu-card .book-btn').forEach(btn=>{
    btn.addEventListener('click', e=>{
        e.preventDefault();
        if(btn.disabled) return;
        const card = btn.closest('.menu-card');
        const id = card.dataset.menuId;
        if(selectedMenus.includes(id)){
            selectedMenus = selectedMenus.filter(x=>x!=id);
            btn.classList.remove('selected'); btn.textContent='Select';
        } else {
            selectedMenus.push(id);
            btn.classList.add('selected'); btn.textContent='Selected';
        }

        // Update hidden inputs
        form.innerHTML = '';
        selectedMenus.forEach(id=>{
            const input = document.createElement('input');
            input.type='hidden';
            input.name='menus[]';
            input.value=id;
            form.appendChild(input);
        });

        const confirmBtn = document.createElement('button');
        confirmBtn.type='submit';
        confirmBtn.className='book-btn';
        confirmBtn.textContent='Confirm Selected Menus';
        form.appendChild(confirmBtn);
    });
});
</script>
</body>
</html>
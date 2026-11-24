<?php
session_start();
if (!isset($_GET['id'])) {
    die("Caterer ID is required.");
}

$catererId = intval($_GET['id']);

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch caterer info
    $stmt = $conn->prepare("SELECT * FROM catering_applications WHERE id = ?");
    $stmt->execute([$catererId]);
    $caterer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$caterer) die("Caterer not found.");

    // 🔥 FIX: Only process the path AFTER $caterer exists
    $path = $caterer['logo'];  // example: "uploads/logos/image.jpg"
    $dir = dirname($path);     // "uploads/logos"
    $file = basename($path);   // "image.jpg"

    // Fetch caterer menus
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE catering_user_id = ?");
    $stmt->execute([$catererId]);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($caterer['catering_name']) ?> | Menu</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet"> -->
<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f8f8;
    color: #333;
    margin: 0;
    padding: 20px;
}
.caterer-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    max-width: 600px;
    margin: 20px auto;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    text-align: center;
}
.caterer-card img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 15px;
}
.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 25px;
    margin-top: 30px;
}
.menu-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    text-align: center;
    transition: 0.3s;
}
.menu-card:hover { transform: translateY(-5px); }
.menu-card h3 { margin: 8px 0; font-size: 18px; font-weight: 600; }
.menu-card p { margin: 5px 0; font-size: 14px; }
.book-btn {
    margin-top: auto;
    padding: 10px;
    background-color: #dcb6a3;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 600;
}
.book-btn:hover { background-color: #c49b88; }
</style>
</head>
<body>

<div class="caterer-card">
    <img 
    src="<?= $dir . '/' . rawurlencode($file) ?>" 
    alt="<?= htmlspecialchars($caterer['catering_name']) ?>">
    <h1><?= htmlspecialchars($caterer['catering_name']) ?></h1>
    <p><strong>Owner:</strong> <?= htmlspecialchars($caterer['owner_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($caterer['email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($caterer['phone']) ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($caterer['address']) ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($caterer['description']) ?></p>
    <p><strong>Cuisine Type:</strong> <?= htmlspecialchars($caterer['cuisine_type']) ?></p>
    <p><strong>Capacity:</strong> <?= htmlspecialchars($caterer['capacity']) ?></p>
</div>

<h2 style="text-align:center; margin-top:40px;">Menu Items</h2>
<div class="menu-grid">
<?php foreach ($menus as $menu): 
    $images = array_filter(array_map('trim', explode(',', $menu['image'])));
?>
    <div class="menu-card" data-menu-id="<?= $menu['item_id'] ?>">
        <div id="carouselMenu<?= $menu['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $img): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= "restaurant/uploads/" . rawurlencode($img) ?>" class="d-block w-100" 
                         style="height:170px; object-fit:cover; border-radius:10px;" 
                         alt="<?= htmlspecialchars($menu['name']) ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php if(count($images) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselMenu<?= $menu['item_id'] ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselMenu<?= $menu['item_id'] ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
                <span class="visually-hidden">Next</span>
            </button>
            <?php endif; ?>
        </div>

        <h3><?= htmlspecialchars($menu['name']) ?></h3>
        <p><?= htmlspecialchars($menu['description']) ?></p>
        <p>₱<?= number_format($menu['price'], 2) ?></p>
        <button class="book-btn" <?= strtolower($menu['status']) !== 'available' ? 'disabled' : '' ?>>
            <?= strtolower($menu['status']) === 'available' ? 'Select' : 'Unavailable' ?>
        </button>
    </div>
<?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

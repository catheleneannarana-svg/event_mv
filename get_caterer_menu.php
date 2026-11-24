<?php
require_once 'db_connection.php'; // your PDO connection

if (!isset($_GET['id'])) exit('Invalid request');

$caterer_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM menu_items WHERE catering_user_id = ?");
$stmt->execute([$caterer_id]);
$menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$menus) {
    echo "<p>No menus available for this caterer.</p>";
    exit;
}

echo '<div class="menu-grid">';
foreach ($menus as $menu) {
    $images = array_filter(array_map('trim', explode(',', $menu['image'])));
    echo '<div class="menu-card">';
    echo '<div id="carousel'.$menu['item_id'].'" class="carousel slide" data-bs-ride="carousel">';
    echo '<div class="carousel-inner">';
    foreach ($images as $i => $img) {
        $active = $i === 0 ? 'active' : '';
        echo '<div class="carousel-item '.$active.'">';
        echo '<img src="../restaurant/uploads/'.rawurlencode($img).'" class="d-block w-100" style="height:170px; object-fit:cover; border-radius:10px;">';
        echo '</div>';
    }
    echo '</div>';
    if (count($images) > 1) {
        echo '<button class="carousel-control-prev" type="button" data-bs-target="#carousel'.$menu['item_id'].'" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
              </button>';
        echo '<button class="carousel-control-next" type="button" data-bs-target="#carousel'.$menu['item_id'].'" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
              </button>';
    }
    echo '</div>'; // carousel
    echo '<h3>'.htmlspecialchars($menu['name']).'</h3>';
    echo '<p>'.htmlspecialchars($menu['description']).'</p>';
    echo '<p>₱'.number_format($menu['price'],2).'</p>';
    echo '<button class="book-btn" '.(strtolower($menu['status'])!=='available'?'disabled':'').'>'
         .(strtolower($menu['status'])==='available'?'Select':'Unavailable').
         '</button>';
    echo '</div>';
}
echo '</div>';
?>

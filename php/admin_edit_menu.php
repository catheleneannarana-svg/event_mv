<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT CONCAT(first_name,' ',COALESCE(middle_name,''),' ',last_name) AS full_name FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        header("Location: admin_catering.php");
        exit();
    }

    // fetch menu
    $stmt = $conn->prepare("SELECT * FROM catering_menus WHERE id = ?");
    $stmt->execute([$id]);
    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$menu) {
        header("Location: admin_catering.php");
        exit();
    }

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['menu_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $meal_type = $_POST['meal_type'] ?? 'Main Course';

        if ($name === '') $errors[] = "Menu name is required.";

        // image replace?
        $imageName = $menu['image'];
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['image/jpeg','image/png','image/webp'];
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) $errors[] = "Image upload error.";
            elseif (!in_array($_FILES['image']['type'], $allowed)) $errors[] = "Only JPG/PNG/WEBP allowed.";
            else {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $target = __DIR__ . '/../uploads/' . $newName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $errors[] = "Failed to move uploaded image.";
                } else {
                    // delete old
                    if (!empty($imageName) && file_exists(__DIR__ . '/../uploads/' . $imageName)) {
                        @unlink(__DIR__ . '/../uploads/' . $imageName);
                    }
                    $imageName = $newName;
                }
            }
        }

        if (empty($errors)) {
            $u = $conn->prepare("UPDATE catering_menus SET menu_name = :name, description = :desc, price = :price, meal_type = :meal, image = :image WHERE id = :id");
            $u->execute([
                ':name' => $name,
                ':desc' => $desc,
                ':price' => $price,
                ':meal' => $meal_type,
                ':image' => $imageName,
                ':id' => $id
            ]);
            header("Location: admin_catering.php");
            exit();
        }
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Menu</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body{font-family:'Poppins',sans-serif;margin:0;padding:0;margin-left:260px;background:#f8f8f8}
.dashboard-header{position:fixed;top:0;left:0;right:0;height:90px;background:#fff;display:flex;justify-content:space-between;align-items:center;padding:0 30px;box-shadow:0 2px 8px rgba(0,0,0,.1);z-index:10}
.container{margin-top:120px;padding:20px}
.card{background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.06)}
.form-control{margin-bottom:10px}
.btn-save{background:#dcb6a3;color:#fff;border:none;padding:8px 14px;border-radius:8px}
.alert{color:#842029;background:#f8d7da;padding:10px;border-radius:6px}
.img-preview{width:160px;height:100px;object-fit:cover;border-radius:6px;margin-bottom:10px}
</style>
</head>
<body>
<header class="dashboard-header">
    <div class="logo"><img src="../img/logo.png" class="logo-img" style="width:100px" alt="Logo"> Event<span style="font-family:'Dancing Script',cursive;color:#dcb6a3">Link</span></div>
</header>

<div class="container">
    <div class="card">
        <h3>Edit Menu</h3>
        <?php if (!empty($errors)): ?><div class="alert"><?= implode('<br>', array_map('htmlspecialchars',$errors)) ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Menu name</label>
            <input class="form-control form-control-sm" type="text" name="menu_name" required value="<?= htmlspecialchars($menu['menu_name']) ?>">

            <label>Description</label>
            <textarea class="form-control form-control-sm" name="description" rows="3"><?= htmlspecialchars($menu['description']) ?></textarea>

            <label>Price</label>
            <input class="form-control form-control-sm" type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($menu['price']) ?>">

            <label>Meal type</label>
            <select class="form-control form-control-sm" name="meal_type">
                <?php foreach (['Appetizer','Main Course','Side Dish','Dessert','Beverages','Salad'] as $mt): ?>
                    <option <?= $menu['meal_type'] === $mt ? 'selected' : '' ?>><?= $mt ?></option>
                <?php endforeach; ?>
            </select>

            <label>Current Image</label><br>
            <?php if (!empty($menu['image']) && file_exists(__DIR__ . '/../uploads/' . $menu['image'])): ?>
                <img class="img-preview" src="../uploads/<?= htmlspecialchars($menu['image']) ?>" alt="">
            <?php else: ?>
                <div style="width:160px;height:100px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#999">No image</div>
            <?php endif; ?>
            <br>
            <label>Replace Image (optional)</label>
            <input class="form-control form-control-sm" type="file" name="image" accept="image/*">

            <div style="margin-top:12px">
                <button class="btn-save" type="submit">Update</button>
                <a href="admin_catering.php" style="margin-left:10px">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

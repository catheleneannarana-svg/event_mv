<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// --- Handle Delete ---
if (isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
    $stmt->execute([$_POST['delete_id']]);
    $message = "Food deleted successfully!";
}

// --- Handle Insert / Edit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $item_id = $_POST['item_id'] ?? null;
    $name = htmlspecialchars($_POST['name']);
    $price = $_POST['price'];
    $desc = htmlspecialchars($_POST['description']);
    $category = $_POST['category'];
    $status = $_POST['status'];

    // Handle multiple image uploads
    $uploadedImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);

        foreach ($_FILES['images']['name'] as $key => $filename) {
            $imageName = time() . '_' . basename($filename);
            $targetFile = $targetDir . $imageName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            if (in_array($imageFileType, ['jpg','jpeg','png','gif'])) {
                move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetFile);
                $uploadedImages[] = $imageName;
            }
        }
    }

    // Keep old images if editing and no new images uploaded
    if ($item_id) {
        $stmt = $conn->prepare("SELECT image FROM menu_items WHERE item_id = ?");
        $stmt->execute([$item_id]);
        $existingImages = $stmt->fetchColumn();
        if ($existingImages) {
            $existingImagesArr = explode(',', $existingImages);
            $uploadedImages = array_merge($existingImagesArr, $uploadedImages);
        }
    }

    $imageString = implode(',', $uploadedImages);

    if ($item_id) {
        $stmt = $conn->prepare("UPDATE menu_items SET name=?, category=?, description=?, price=?, image=?, status=? WHERE item_id=?");
        $stmt->execute([$name, $category, $desc, $price, $imageString, $status, $item_id]);
        $message = "Food updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO menu_items (name, category, description, price, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $category, $desc, $price, $imageString, $status]);
        $message = "Food posted successfully!";
    }
}

// --- Fetch All Food Items ---
$stmt = $conn->query("SELECT * FROM menu_items ORDER BY item_id DESC");
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Food Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family: Arial; padding: 20px; }
.success { background: #c8f7c5; padding: 10px; margin-bottom: 10px; border-radius:5px; }
.carousel-item img { height: 350px; object-fit: cover; }
.food-card { margin-bottom: 30px; padding: 15px; border:1px solid #ddd; border-radius:8px; }
.modal-body { overflow: visible !important; } /* Fix dropdown inside modal */
.modal .form-select { z-index: 1051; } /* Fix dropdown overlapping */
.img-preview { width: 100px; height: 100px; object-fit: cover; margin-right:5px; margin-bottom:5px; border:1px solid #ccc; border-radius:5px; }
</style>
</head>
<body>

<h2>Post a New Food Item</h2>
<?php if (!empty($message)) echo "<div class='success'>$message</div>"; ?>

<form action="" method="POST" enctype="multipart/form-data" class="mb-5">
    <input type="hidden" name="item_id" value="">
    <div class="mb-2">
        <label>Category:</label>
        <select name="category" class="form-select" required>
            <option value="Appetizer">Appetizer</option>
            <option value="Main Course">Main Course</option>
            <option value="Dessert">Dessert</option>
            <option value="Beverage">Beverage</option>
        </select>
    </div>

    <div class="mb-2">
        <label>Food Name:</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-2">
        <label>Price:</label>
        <input type="number" name="price" class="form-control" step="0.01" required>
    </div>

    <div class="mb-2">
        <label>Description:</label>
        <textarea name="description" class="form-control" required></textarea>
    </div>

    <div class="mb-2">
        <label>Food Images (optional multiple):</label>
        <input type="file" name="images[]" multiple class="form-control" accept="image/*" onchange="previewImages(this)">
        <div id="imagePreview" class="mt-2"></div>
    </div>

    <div class="mb-2">
         <label>Status:</label>
         <select name="status" class="form-select" required>
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
         </select>
    </div>

    <button type="submit" class="btn btn-success">Save Food</button>
</form>

<hr>
<h2>All Foods</h2>

<?php if ($foods): ?>
    <?php foreach ($foods as $f): 
        $images = explode(',', $f['image']);
    ?>
    <div class="food-card">
        <div id="carousel<?= $f['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $i => $img): ?>
                    <div class="carousel-item <?= $i===0?'active':'' ?>">
                        <img src="uploads/<?= $img ?>" class="d-block w-100" alt="<?= $f['name'] ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $f['item_id'] ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $f['item_id'] ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <h5 class="mt-2"><?= $f['name'] ?> (<?= $f['category'] ?>)</h5>
        <p>₱<?= number_format($f['price'],2) ?> - <?= $f['description'] ?></p>

        <form action="" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this item?');">
            <input type="hidden" name="delete_id" value="<?= $f['item_id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
        <button class="btn btn-primary btn-sm" 
            data-bs-toggle="modal" 
            data-bs-target="#editModal" 
            data-id="<?= $f['item_id'] ?>" 
            data-name="<?= $f['name'] ?>" 
            data-category="<?= $f['category'] ?>" 
            data-price="<?= $f['price'] ?>" 
            data-desc="<?= htmlspecialchars($f['description'], ENT_QUOTES) ?>"
            data-status="<?= $f['status'] ?>">
            Edit
        </button>
    </div>
    <?php endforeach; ?>
<?php else: ?>
<p>No food items posted yet.</p>
<?php endif; ?>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Food</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="item_id" id="modalItemId">

        <div class="mb-2">
            <label>Category:</label>
            <select name="category" id="modalCategory" class="form-select" required>
                <option value="Appetizer">Appetizer</option>
                <option value="Main Course">Main Course</option>
                <option value="Dessert">Dessert</option>
                <option value="Beverage">Beverage</option>
            </select>
        </div>

        <div class="mb-2">
            <label>Food Name:</label>
            <input type="text" name="name" id="modalName" class="form-control" required>
        </div>

        <div class="mb-2">
            <label>Price:</label>
            <input type="number" name="price" id="modalPrice" class="form-control" step="0.01" required>
        </div>

        <div class="mb-2">
            <label>Description:</label>
            <textarea name="description" id="modalDesc" class="form-control" required></textarea>
        </div>

        <div class="mb-2">
            <label>Food Images (optional multiple):</label>
            <input type="file" name="images[]" multiple class="form-control" accept="image/*" onchange="previewImages(this)">
            <div id="editImagePreview" class="mt-2"></div>
        </div>

        <div class="mb-2">
            <label>Status:</label>
            <select name="status" id="modalStatus" class="form-select" required>
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update Food</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Fill modal when Edit button clicked
var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    document.getElementById('modalItemId').value = button.getAttribute('data-id');
    document.getElementById('modalName').value = button.getAttribute('data-name');
    document.getElementById('modalCategory').value = button.getAttribute('data-category');
    document.getElementById('modalPrice').value = button.getAttribute('data-price');
    document.getElementById('modalDesc').value = button.getAttribute('data-desc');
    document.getElementById('modalStatus').value = button.getAttribute('data-status');
});

// Preview images before upload
function previewImages(input) {
    var preview = input.closest('form').querySelector('#imagePreview') || input.closest('form').querySelector('#editImagePreview');
    preview.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = "img-preview";
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>

</body>
</html>

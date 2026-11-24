<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle actions
    $action = $_GET['action'] ?? '';
    $service_id = intval($_GET['service_id'] ?? 0);
    $id = intval($_GET['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'add') {
            $name = trim($_POST['inclusion_name']); 
            $desc = trim($_POST['description']);
            $price = floatval($_POST['price']);
            if ($name !== '' && $price > 0) {
                $stmt = $conn->prepare("INSERT INTO service_inclusions (service_id, inclusion_name, description, price) VALUES (:service_id, :name, :desc, :price)");
                $stmt->execute([':service_id'=>$service_id, ':name'=>$name, ':desc'=>$desc, ':price'=>$price]);
            }
            header("Location: service_inclusions.php");
            exit();
        }

        if ($action === 'edit') {
            $name = trim($_POST['inclusion_name']);
            $desc = trim($_POST['description']);
            $price = floatval($_POST['price']);
            if ($name !== '' && $price > 0) {
                $stmt = $conn->prepare("UPDATE service_inclusions SET inclusion_name=:name, description=:desc, price=:price WHERE inclusion_id=:id");
                $stmt->execute([':name'=>$name, ':desc'=>$desc, ':price'=>$price, ':id'=>$id]);
            }
            header("Location: service_inclusions.php");
            exit();
        }
    }

    // Delete inclusion
    if ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM service_inclusions WHERE inclusion_id=:id");
        $stmt->execute([':id'=>$id]);
        header("Location: service_inclusions.php");
        exit();
    }

    // Fetch all services
    $stmt = $conn->query("SELECT * FROM services ORDER BY service_id DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all inclusions grouped by service
    $stmt2 = $conn->query("SELECT * FROM service_inclusions ORDER BY inclusion_name ASC");
    $all_inclusions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $inclusions_by_service = [];
    foreach ($all_inclusions as $inc) {
        $inclusions_by_service[$inc['service_id']][] = $inc;
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Service Inclusions Management</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<style>
body { font-family:Poppins,sans-serif; margin-left:260px; background:#f0f2f5; padding:20px; }
.card { background:#fff; padding:15px; margin-bottom:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.1); }
.table th, .table td { text-align:center; vertical-align:middle; }
.btn { padding:5px 10px; border:none; border-radius:5px; margin-right:3px; }
.btn-primary { background:#dcb6a3; color:#fff; }
.btn-primary:hover { background:#c49b88; }
.btn-info { background:#ffb74d; color:#000; }
.btn-info:hover { background:#dcb6a3; }
.btn-danger { background:#f56565; color:#fff; }
.btn-danger:hover { background:#e04e4e; }
input { padding:5px; margin-right:5px; border-radius:5px; border:1px solid #ccc; }
</style>
</head>
<body>

<h2>Manage Service Inclusions</h2>

<?php foreach($services as $s): ?>
<div class="card">
    <h3><?= htmlspecialchars($s['service_name']) ?> Inclusions</h3>

    <!-- Table of inclusions -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($inclusions_by_service[$s['service_id']])): ?>
                <?php foreach($inclusions_by_service[$s['service_id']] as $inc): ?>
                    <?php if($action==='edit' && $id==$inc['inclusion_id']): ?>
                        <tr>
                        <form method="POST" action="?action=edit&id=<?= $id ?>&service_id=<?= $s['service_id'] ?>">
                            <td><?= $inc['inclusion_id'] ?></td>
                            <td><input type="text" name="inclusion_name" value="<?= htmlspecialchars($inc['inclusion_name']) ?>"></td>
                            <td><input type="text" name="description" value="<?= htmlspecialchars($inc['description']) ?>"></td>
                            <td><input type="number" step="0.01" name="price" value="<?= $inc['price'] ?>"></td>
                            <td>
                                <button class="btn btn-primary btn-sm">💾 Save</button>
                                <a href="service_inclusions.php" class="btn btn-info btn-sm">Cancel</a>
                            </td>
                        </form>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td><?= $inc['inclusion_id'] ?></td>
                            <td><?= htmlspecialchars($inc['inclusion_name']) ?></td>
                            <td><?= htmlspecialchars($inc['description']) ?></td>
                            <td>₱<?= number_format($inc['price'],2) ?></td>
                            <td>
                                <a href="?action=edit&id=<?= $inc['inclusion_id'] ?>&service_id=<?= $s['service_id'] ?>" class="btn btn-info btn-sm">✏️ Edit</a>
                                <a href="?action=delete&id=<?= $inc['inclusion_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this inclusion?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No inclusions added yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Add new inclusion form -->
    <form method="POST" action="?action=add&service_id=<?= $s['service_id'] ?>" class="mt-2">
        <input type="text" name="inclusion_name" placeholder="Inclusion Name" required>
        <input type="text" name="description" placeholder="Description">
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <button class="btn btn-primary btn-sm">➕ Add Inclusion</button>
    </form>
</div>
<?php endforeach; ?>

</body>
</html>

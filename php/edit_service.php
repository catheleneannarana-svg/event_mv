<?php
if (!isset($_GET['id'])) {
    die("⚠️ No service ID provided.");
}

$service_id = $_GET['id'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM services WHERE service_id = :id");
    $stmt->execute([':id' => $service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        die("❌ Service not found.");
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = trim($_POST['service_name']);
        $description = trim($_POST['description']);
        $rate = floatval($_POST['rate']);
        $assigned_count = intval($_POST['assigned_count']);
        $status = $_POST['status'];

        if (!empty($name) && $assigned_count >= 0 && $rate >= 0) {
            $update = $conn->prepare("
                UPDATE services 
                SET service_name = :name, description = :desc, rate = :rate, status = :status, assigned_staff_count = :assigned
                WHERE service_id = :id
            ");
            $update->execute([
                ':name' => $name,
                ':desc' => $description,
                ':rate' => $rate,
                ':status' => $status,
                ':assigned' => $assigned_count,
                ':id' => $service_id
            ]);

            header("Location: services.php?updated=1");
            exit;
        } else {
            $error = "⚠️ Please fill in all fields correctly.";
        }
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Service</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">

<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background: #f7f6f4ff; /* soft yellow background */
    color: #333;
}

/* Back link */
.back-text {
    position: fixed;
    top: 40px;
    left: 40px;
    font-weight: 700;
    font-size: 20px;
    color: #232322ff;
    text-decoration: none;
    z-index: 1000;
}
.back-text:hover { 
    transform: translateX(-5px);
    transition: 0.2s;
}

/* Main container */
.container {
    margin: 100px auto 0;
    padding: 20px;
    max-width: 600px;
}

.card {
    background: #f3f0e8ff; /* yellow card */
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 30px;
    color: #333;
    padding-top: 0px;
}

.card h2 {
    font-weight: bold;
    color: #ffb74d;
    margin-bottom: 25px;
}

.form-label {
    margin-top: 10px;
    font-weight: 500;
}

/* Buttons */
.btn-primary {
    background-color: #ffb74d;
    border-color: #ffb74d;
    color: #1a1a1a;
    font-weight: 600;
    width: 100%;
    transition: 0.3s;
}

.btn-primary:hover {
    background-color: #ffc107;
    border-color: #ffc107;
}

.btn-secondary {
    background-color: #ffe082;
    color: #1a1a1a;
    border: none;
    font-weight: 500;
    transition: 0.3s;
}

.btn-secondary:hover {
    background-color: #ffd54f;
}

.alert {
    border-radius: 10px;
    padding: 10px 15px;
    margin-bottom: 20px;
}
</style>
</head>

<body>

<a href="services.php" class="back-text">⬅ Back</a>

<div class="container">
    <div class="card">

        <h2>✏️ Edit Service</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-warning"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <label class="form-label">Service Name</label>
            <input type="text" name="service_name" class="form-control"
                   value="<?= htmlspecialchars($service['service_name']) ?>" required>

            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($service['description']) ?></textarea>

            <label class="form-label">Rate (₱)</label>
            <input type="number" name="rate" class="form-control" min="0" step="0.01"
                   value="<?= htmlspecialchars($service['rate']) ?>" required>

            <label class="form-label">Assign Staff (number)</label>
            <input type="number" name="assigned_count" class="form-control" min="0"
                   value="<?= htmlspecialchars($service['assigned_staff_count']) ?>" required>

            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active"   <?= $service['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $service['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            </div>

        </form>
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

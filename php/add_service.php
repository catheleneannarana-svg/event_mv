<?php
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $error = "";
    $success = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
        $description  = isset($_POST['description']) ? trim($_POST['description']) : '';
        $assigned_count = isset($_POST['assigned_count']) ? max(0,intval($_POST['assigned_count'])) : 0;
        $rate = isset($_POST['rate']) ? max(0,floatval($_POST['rate'])) : 0.00;

        if (empty($service_name)) {
            $error = "Service name is required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO services (service_name, description, rate, status, assigned_staff_count) VALUES (?, ?, ?, 'active', ?)");
            $stmt->execute([$service_name, $description, $rate, $assigned_count]);

            $success = "✅ Service added successfully!";
            header("refresh:1;url=services.php");
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
<title>Add New Service</title>
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

<div class="container">
    <a href="services.php" class="back-text">⬅ Back</a>
    <div class="card">
        <h2>➕ Add New Service</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label class="form-label">Service Name</label>
            <input type="text" name="service_name" class="form-control" placeholder="e.g. Photographer" required>

            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the service"></textarea>

            <label class="form-label">Rate (₱)</label>
            <input type="number" name="rate" class="form-control" min="0" step="0.01" value="0.00" required>

            <label class="form-label">Assign Staff (number)</label>
            <input type="number" name="assigned_count" class="form-control" min="0" value="0" required>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary">💾 Save Service</button>
            </div>
        </form>
    </div>
</div>

<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

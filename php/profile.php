<?php
session_start();

// Redirect if not logged in as customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = (int) $_SESSION['user_id'];

    // Fetch user info
    $stmt = $conn->prepare("
        SELECT first_name, middle_name, last_name, email, contact_number
        FROM users 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        die("Error: User not found!");
    }

    $fullName = trim(
        ($customer['first_name'] ?? '') . ' ' .
        ($customer['middle_name'] ?? '') . ' ' .
        ($customer['last_name'] ?? '')
    );

} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    /* Remove left margin */
    /* margin-left: 260px; */
    background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)),
                url('../img/event1.webp') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
    position: relative;
}

/* Back link fixed to the left */
.back-text {
    position: fixed;
    top: 45px;
    left: 40px;
    color: #fff;
    font-weight: 700;
    font-size: 20px;
    text-decoration: none;
    z-index: 1000;
}
.back-text:hover i {
    transform: translateX(-5px);
    transition: 0.2s;
}


/* MAIN CONTENT */
.container {
    margin: 100px auto 0; /* center horizontally */
    padding: 20px;
    max-width: 700px;
}
.card {
    background: rgba(255,255,255,0.95);
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 20px;
    color: #333;
}

.card h2 {
    font-weight: bold;
    color: #dcb6a3;
    margin-bottom: 20px;
}
.form-label {
    margin-top: 10px;
    font-weight: 500;
}

/* Profile Update Button */
.btn-primary {
    background-color: #dcb6a3;
    border-color: #dcb6a3;
    color: white;
    font-weight: 500;
    transition: 0.3s;
    width: 100%;
}

.btn-primary:hover {
    background-color: #c49b88;
    border-color: #c49b88;
    color: white;
}

</style>
</head>
<body>

<div class="container">
    <a href="customer_dashboard.php" class="back-text">⬅ Back</a>
    <div class="card">
        <h2>My Profile</h2>
        <form action="update_profile.php" method="POST">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($customer['first_name'] ?? '') ?>" required>

            <label class="form-label">Middle Name (optional)</label>
            <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($customer['middle_name'] ?? '') ?>">

            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($customer['last_name'] ?? '') ?>" required>

            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" required>

            <label class="form-label">Phone Number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($customer['contact_number'] ?? '') ?>">

            <button type="submit" class="btn btn-primary mt-3 w-100">Update Profile</button>
        </form>
    </div>
</div>


<script src="../bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();

// Only allow logged-in customers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $user_id = (int) $_SESSION['user_id'];

        // Get form data safely
        $first_name  = $_POST['first_name'] ?? '';
        $middle_name = $_POST['middle_name'] ?? '';
        $last_name   = $_POST['last_name'] ?? '';
        $email       = $_POST['email'] ?? '';
        $contact     = $_POST['contact_number'] ?? '';

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email)) {
            throw new Exception("First name, last name, and email are required.");
        }

        // Update query
        $stmt = $conn->prepare("
            UPDATE users 
            SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_number = ?
            WHERE user_id = ?
        ");

        $stmt->execute([$first_name, $middle_name, $last_name, $email, $contact, $user_id]);

        // Optional: update session full name for sidebar
        $_SESSION['full_name'] = trim("$first_name $middle_name $last_name");

        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
        exit();

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: profile.php");
    exit();
}
?>


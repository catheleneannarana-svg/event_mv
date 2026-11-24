<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') exit('error');

if(isset($_POST['id'])){
    try {
        $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE admin_notifications SET is_read=1 WHERE notification_id=:id");
        $stmt->execute([':id'=>$_POST['id']]);
        echo 'success';

    } catch(PDOException $e){ echo 'error'; }
}
?>

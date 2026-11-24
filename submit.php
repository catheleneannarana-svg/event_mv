<?php


try {
     $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to upload BIR certificate
function uploadFile($file){
    $targetDir = "uploads/"; // Make sure this folder exists
    if(!is_dir($targetDir)){
        mkdir($targetDir, 0755, true);
    }

    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . time() . "_" . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg','jpeg','png','pdf'];
    if(!in_array($fileType, $allowedTypes)){
        return false; // Invalid file type
    }

    if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
        return $targetFilePath;
    } else {
        return false;
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Check if BIR certificate is uploaded
    if(!isset($_FILES['bir_proof']) || $_FILES['bir_proof']['error'] != 0){
        die("Error: BIR Certificate is required.");
    }

    $catering_name = $_POST['catering_name'];
    $owner_name = $_POST['owner_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $cuisine = $_POST['cuisine_type'];
    $capacity = $_POST['capacity'];

    $bir_proof_path = uploadFile($_FILES['bir_proof']);
    if(!$bir_proof_path){
        die("Error uploading BIR Certificate. Allowed file types: JPG, PNG, PDF.");
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO catering_applications 
        (catering_name, owner_name, email, phone, address, cuisine_type, capacity, bir_proof)
        VALUES (:catering_name, :owner_name, :email, :phone, :address, :cuisine_type, :capacity, :bir_proof)");

    $stmt->bindParam(':catering_name', $catering_name);
    $stmt->bindParam(':owner_name', $owner_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':cuisine_type', $cuisine);
    $stmt->bindParam(':capacity', $capacity);
    $stmt->bindParam(':bir_proof', $bir_proof_path);

    if($stmt->execute()){
    // Show alert and redirect to index.php
    echo "<script>
            alert('Your application was submitted successfully! Please wait for admin approval.');
            window.location.href='index.html';
          </script>";
    exit();
} else {
    echo "Error submitting application.";

}
}
?>

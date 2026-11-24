<?php
session_start();

if (!isset($_SESSION['catering_id'])) {
    header("Location: ../php/login.php");
    exit();
}

$catering_id = $_SESSION['catering_id'];

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch profile + application info
$stmt = $conn->prepare("
    SELECT cu.username, cu.email, 
           ca.catering_name, ca.owner_name, ca.phone, ca.description, 
           ca.bir_proof, ca.logo
    FROM catering_users cu
    JOIN catering_applications ca ON cu.catering_id = ca.id
    WHERE cu.catering_id = :id
");
$stmt->execute([':id' => $catering_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);


// -------------------- SAVE PROFILE --------------------
if (isset($_POST['save_profile'])) {
    $phone = $_POST['phone'];
    $desc = $_POST['description'];

    // Handle logo upload
    $logoFile = $profile['logo']; // default: old image

    if (!empty($_FILES['logo']['name'])) {
        $targetDir = "../uploads/logos/";
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["logo"]["name"]);
        $targetFile = $targetDir . $fileName;

        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Validate image type
        $allowed = ["jpg","jpeg","png","gif"];
        if (in_array($imageFileType, $allowed)) {
            move_uploaded_file($_FILES["logo"]["tmp_name"], $targetFile);
            $logoFile = "uploads/logos/" . $fileName;
        }  
    }

    $stmt = $conn->prepare("
        UPDATE catering_applications 
        SET phone=:phone, description=:description, logo=:logo 
        WHERE id=:id");
        
    $stmt->execute([
        ':phone' => $phone,
        ':description' => $desc,
        ':logo' => $logoFile,
        ':id' => $catering_id
    ]);

    echo "<script>alert('Profile updated successfully!');</script>";
    header("Refresh:0");
    exit;
}


// -------------------- CHANGE PASSWORD --------------------
if (isset($_POST['change_pass'])) {

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $newpass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE catering_users 
                                SET password=:pass 
                                WHERE catering_id=:id");
        $stmt->execute([
            ':pass' => $newpass,
            ':id' => $catering_id
        ]);

        echo "<script>alert('Password updated successfully!');</script>";
        header("Refresh:0");
        exit;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Catering Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background: #f4f5f7; }
    .container-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 3px 8px rgba(0,0,0,.1);
    }
    .label-title { font-weight: bold; color: #444; }
    .bir-img { max-width: 100%; border-radius: 10px; }
    .logo-preview {
        width: 120px;
        height: 120px;
        border-radius: 10px;
        object-fit: cover;
        border: 2px solid #ddd;
        margin-bottom: 10px;
    }
</style>
</head>

<body>

<div class="container mt-4">
    <h2 class="mb-4">👤 Profile & BIR Certificate</h2>

    <div class="container-box">

        <!-- BASIC PROFILE -->
        <h4>Basic Information</h4>
        <hr>

        <form method="POST" enctype="multipart/form-data">

            <!-- LOGO Preview -->
            <label class="label-title">Catering Logo</label><br>

            <?php if (!empty($profile['logo'])): ?>
                <img src="../<?= $profile['logo'] ?>" class="logo-preview">
            <?php else: ?>
                <p class="text-muted">No logo uploaded.</p>
            <?php endif; ?>

            <input type="file" name="logo" class="form-control mb-3">

            <!-- INFO FIELDS -->
            <div class="mb-3">
                <label class="label-title">Catering Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['catering_name']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="label-title">Owner Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['owner_name']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="label-title">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($profile['email']) ?>" disabled>
            </div>

            <div class="mb-3">
                <label class="label-title">Phone</label>
                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($profile['phone']) ?>">
            </div>

            <div class="mb-3">
                <label class="label-title">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($profile['description']) ?></textarea>
            </div>

            <button name="save_profile" class="btn btn-primary">Save Profile</button>
        </form>


        <hr class="my-4">

        <!-- BIR CERTIFICATE -->
        <h4>BIR Certificate</h4>
        <p>Uploaded during application</p>

        <?php if ($profile['bir_proof']): ?>
            <button class="btn btn-secondary" onclick="openBIR()">View BIR Certificate</button>
        <?php else: ?>
            <p class="text-danger">No BIR certificate uploaded.</p>
        <?php endif; ?>

        <hr class="my-4">

        <!-- PASSWORD -->
        <h4>Change Password</h4>

        <form method="POST" onsubmit="return validatePassword()">

            <div class="mb-3">
                <label class="label-title">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="label-title">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>

            <button name="change_pass" class="btn btn-dark">Update Password</button>
        </form>


    </div>
</div>


<!-- BIR MODAL -->
<div class="modal fade" id="birModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">BIR Certificate Preview</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img src="../uploads/<?= htmlspecialchars(basename($profile['bir_proof'])) ?>" class="bir-img">
      </div>
    </div>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function validatePassword() {
    let pass = document.getElementById("new_password").value;
    let confirm = document.getElementById("confirm_password").value;

    if (pass !== confirm) {
        alert("Passwords do not match!");
        return false;
    }
    return true;
}
function openBIR() {
    const modal = new bootstrap.Modal(document.getElementById('birModal'));
    modal.show();
}
</script>

</body>
</html>

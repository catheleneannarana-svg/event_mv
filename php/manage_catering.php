<?php
session_start();

// --- PHPMailer ---
require __DIR__ . '/src/PHPMailer.php';
require __DIR__ . '/src/SMTP.php';
require __DIR__ . '/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Database Connection ---
try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// --- Handle Approve/Reject ---
if(isset($_GET['action']) && isset($_GET['id'])){
    $id = $_GET['id'];

    // Fetch application first
    $stmt = $conn->prepare("SELECT * FROM catering_applications WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$app){
        header("Location: manage_catering.php");
        exit;
    }

    if($_GET['action'] == 'approve'){
        // Update status
        $stmtUpdate = $conn->prepare("UPDATE catering_applications SET status='Approved' WHERE id=:id");
        $stmtUpdate->execute([':id' => $id]);

        // --- Generate username & password ---
        $username = strtolower(str_replace(' ', '', $app['catering_name']));
        $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);

        // --- Check if email already exists ---
        $stmtCheck = $conn->prepare("SELECT * FROM catering_users WHERE email=:email");
        $stmtCheck->execute([':email' => $app['email']]);
        $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if(!$existing){
            // Insert into catering_users
            $stmtUser = $conn->prepare("
                INSERT INTO catering_users (catering_id, username, password, email)
                VALUES (:catering_id, :username, :password, :email)
            ");
            $stmtUser->execute([
                ':catering_id' => $app['id'],
                ':username'    => $username,
                ':password'    => $hashedPass,
                ':email'       => $app['email']
            ]);
        }

        // --- Send Email ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lilamcathlene@gmail.com'; // <-- Replace with your Gmail
            $mail->Password   = 'uarukkfpbsxtgcrs';     // <-- Replace with App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587; 

            $mail->setFrom('yourgmail@gmail.com', 'Event Management');
            $mail->addAddress($app['email'], $app['owner_name']);

            $mail->isHTML(true);
            $mail->Subject = 'Your Catering Account Has Been Approved!';
            $mail->Body    = "
                Hello {$app['owner_name']},<br><br>
                Your catering service has been approved!<br>
                You may now log in using the following credentials:<br><br>
                <strong>Username:</strong> {$username}<br>
                <strong>Password:</strong> {$password}<br><br>
                Please log in and change your password immediately.<br><br>
                Thank you!
            ";

            $mail->send();
        } catch (Exception $e) {
            echo "<script>alert('Email could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
        }

        header("Location: manage_catering.php?success=1");
        exit;

    } elseif($_GET['action'] == 'reject'){
        $stmtUpdate = $conn->prepare("UPDATE catering_applications SET status='Rejected' WHERE id=:id");
        $stmtUpdate->execute([':id' => $id]);
        header("Location: manage_catering.php");
        exit;
    }
}

// --- Fetch all applications ---
$stmt = $conn->prepare("SELECT * FROM catering_applications ORDER BY status ASC, id DESC");
$stmt->execute();
$cateringApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Catering Applications</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #2196F3; color: #fff; }
a.button { text-decoration: none; padding: 5px 10px; border-radius: 5px; color: #fff; }
.approve { background-color: #4CAF50; }
.reject { background-color: #f44336; }
a.button:hover { opacity: 0.8; }
a.bir-link { color: #2196F3; text-decoration: underline; cursor: pointer; }

/* Modal */
.modal { display: none; position: fixed; z-index: 9999; padding-top: 60px; left:0; top:0; width:100%; height:100%; overflow:auto; background-color: rgba(0,0,0,0.8);}
.modal-content { margin:auto; display:block; max-width:80%; max-height:80%; border-radius:5px; }
#caption { text-align:center; color:#fff; margin-top:10px; font-size:18px;}
.close { position:absolute; top:20px; right:35px; color:#fff; font-size:40px; font-weight:bold; cursor:pointer;}
</style>
</head>
<body>

<h1>Manage Catering Applications</h1>

<?php if(isset($_GET['success'])): ?>
<script>alert('Application approved! Login details sent via email.');</script>
<?php endif; ?>

<table>
    <tr>
        <th>ID</th>
        <th>Catering Name</th>
        <th>Owner</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Status</th>
        <th>BIR Certificate</th>
        <th>Actions</th>
    </tr>

    <?php if($cateringApps): ?>
        <?php foreach($cateringApps as $app): ?>
            <tr>
                <td><?= $app['id'] ?></td>
                <td><?= htmlspecialchars($app['catering_name']) ?></td>
                <td><?= htmlspecialchars($app['owner_name']) ?></td>
                <td><?= htmlspecialchars($app['email']) ?></td>
                <td><?= htmlspecialchars($app['phone']) ?></td>
                <td><?= $app['status'] ?></td>
                <td>
                    <?php if($app['bir_proof']): ?>
                        <a class="bir-link" href="javascript:void(0);" 
                        onclick="openModal('show_file.php?file=<?= urlencode(basename($app['bir_proof'])) ?>', '<?= htmlspecialchars($app['catering_name']) ?>')">
                            View BIR Certificate
                        </a>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($app['status'] == 'Pending'): ?>
                        <a href="?action=approve&id=<?= $app['id'] ?>" class="button approve">Approve</a>
                        <a href="?action=reject&id=<?= $app['id'] ?>" class="button reject">Reject</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8">No catering applications found.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Modal -->
<div id="imageModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImg">
  <div id="caption"></div>
</div>

<script>
function openModal(imgSrc, captionText){
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImg");
    var caption = document.getElementById("caption");

    modalImg.src = imgSrc;
    caption.innerHTML = captionText;
    modal.style.display = "block";
}

function closeModal(){
    document.getElementById("imageModal").style.display = "none";
}
</script>

</body>
</html>

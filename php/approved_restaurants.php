<?php

try {
     $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch approved restaurants
$stmt = $conn->prepare("SELECT * FROM catering_applications WHERE status = 'Approved' ORDER BY catering_name ASC");
$stmt->execute();
$approvedRestaurants = $stmt->fetchAll(PDO::FETCH_ASSOC); 
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Approved Restaurants</title>
<style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
    .restaurant-card { 
        background: #fff; 
        border-radius: 8px; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
        padding: 20px; 
        margin-bottom: 15px; 
    }
    h2 { margin: 0 0 10px; }
    p { margin: 5px 0; }
    a.view-bir { color: #2196F3; text-decoration: none; }
   
.modal {
    display: none; 
    position: fixed; 
    z-index: 9999; 
    padding-top: 60px; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%;
    overflow: auto; 
    background-color: rgba(0,0,0,0.8); 
}
.modal-content {
    margin: auto;
    display: block;
    max-width: 80%;
    max-height: 80%;
    border-radius: 5px;
}
#caption {
    text-align: center;
    color: #fff;
    margin-top: 10px;
    font-size: 18px;
}
.close {
    position: absolute;
    top: 20px;
    right: 35px;
    color: #fff;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}
</style>

</style>
</head>
<body>
    <h1>Approved Catering Services</h1>

    <?php if($approvedRestaurants): ?>
        <?php foreach($approvedRestaurants as $restaurant): ?>
            <div class="restaurant-card">
                <h2><?= htmlspecialchars($restaurant['catering_name']) ?></h2>
                <p><strong>Owner:</strong> <?= htmlspecialchars($restaurant['owner_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($restaurant['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($restaurant['phone']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>
                <p><strong>Cuisine:</strong> <?= htmlspecialchars($restaurant['cuisine_type']) ?></p>
                <p><strong>Capacity:</strong> <?= $restaurant['capacity'] ?> guests</p>
               <p>
    <p>
    <a class="view-bir" href="javascript:void(0);"
       onclick="openModal(
           'show_file.php?file=<?= urlencode(basename($restaurant['bir_proof'])) ?>',
           '<?= htmlspecialchars($restaurant['catering_name']) ?>'
       )">
        View BIR Certificate
    </a>
</p>

</p>


            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No approved catering services found.</p>
    <?php endif; ?>


<!-- Modal -->
<div id="imageModal" class="modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImg">
  <div id="caption"></div>
</div>

<script>
function openModal(imgSrc, captionText) {
    var modal = document.getElementById("imageModal");
    var modalImg = document.getElementById("modalImg");
    var caption = document.getElementById("caption");

    modalImg.src = imgSrc;       
    caption.innerHTML = captionText;
    modal.style.display = "block";
}

function closeModal() {
    document.getElementById("imageModal").style.display = "none";
}

</script>

</body>
</html>

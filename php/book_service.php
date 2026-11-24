<?php
session_start();

// Redirect if not logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle theme selection
    if (isset($_POST['theme_id'])) {
        $_SESSION['selected_theme'] = $_POST['theme_id'];
    }

    // Handle services, inclusions, and schedule
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['services'])) {
        $_SESSION['selected_services'] = $_POST['services'];
        $_SESSION['selected_inclusions'] = $_POST['selected_inclusion'] ?? [];
        $_SESSION['selected_schedule'] = $_POST['selected_schedule'] ?? [];
        header("Location: catering.php");
        exit();
    }

    // Fetch all themes
    $themes = $conn->query("SELECT * FROM themes ORDER BY theme_name")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all services
    $services = $conn->query("SELECT * FROM services ORDER BY service_name")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch inclusions for each service
    $inclusionMap = [];
    $stmt = $conn->query("SELECT * FROM service_inclusions ORDER BY inclusion_name");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $inc) {
        $inclusionMap[$inc['service_id']][] = $inc;
    }

    // Fetch selected theme if exists
    $selected_theme = null;
    if (isset($_SESSION['selected_theme'])) {
        $stmt = $conn->prepare("SELECT * FROM themes WHERE theme_id = ?");
        $stmt->execute([$_SESSION['selected_theme']]);
        $selected_theme = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch customer info
    $stmt = $conn->prepare("SELECT CONCAT(first_name,' ',COALESCE(middle_name,''),' ',last_name) AS full_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book a Service</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; margin:0; padding:0; margin-left:260px; background:#f8f8f8; color:#333; }
.dashboard-header { position:fixed; top:0; left:0; right:0; height:90px; background:#fff; display:flex; justify-content:space-between; align-items:center; padding:0 30px; box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:10; }
.dashboard-header .logo { display:flex; align-items:center; gap:10px; font-size:28px; font-weight:700; color:#333; }
.dashboard-header .logo-img { width:100px; }
.header-profile { display:flex; flex-direction:column; align-items:center; gap:5px; }
.profile-link { text-decoration:none; color:#333; display:flex; flex-direction:column; align-items:center; }
.profile-circle { width:45px; height:45px; border-radius:50%; background:#dcb6a3; display:flex; justify-content:center; align-items:center; font-weight:bold; color:white; font-size:18px; transition:0.3s; }
.profile-circle:hover { background-color:#c49b88; }
.profile-name { font-size:12px; font-weight:500; text-align:center; max-width:80px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sidebar { width:260px; height:100vh; position:fixed; top:90px; left:0; background:#fff; color:#333; padding-top:10px; box-shadow:2px 0 5px rgba(0,0,0,0.1); }
.sidebar h2 { padding-left:20px; margin-top:0; margin-bottom:20px; font-family:cursive; color:#dcb6a3; font-size:28px; }
.sidebar-nav { list-style:none; padding:0 10px; display:flex; flex-direction:column; gap:10px; }
.sidebar-btn { display:block; width:100%; padding:12px 20px; text-decoration:none; color:#333; background:#f4f4f4; border-radius:8px; font-weight:500; transition:0.3s; }
.sidebar-btn:hover { background-color:#dcb6a3; color:#fff; }
.sidebar-btn.active { background-color:#dcb6a3; color:white; font-weight:600; }
.container { padding:20px; padding-top:120px; }
.card { background:#fff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); padding:15px; transition:0.3s; position:relative; }
.card:hover { transform:scale(1.02); }
.btn-primary { background:#dcb6a3; border-color:#dcb6a3; color:white; font-weight:700; transition:0.3s; }
.btn-primary:hover { background:#c49b88; border-color:#c49b88; }
.btn-select { width:100%; margin-top:10px; background:#dcb6a3; color:white; font-weight:700; border-radius:6px; border:none; transition:0.3s; }
.btn-select:hover { background:#c49b88; }
.btn-select.active { background:#e49c80ff !important; color:white; }
input[type="checkbox"][name="services[]"] { display:none; }
.checkmark { position:absolute; top:10px; right:15px; background:#28a745; color:#fff; font-weight:bold; border-radius:50%; width:25px; height:25px; display:flex; justify-content:center; align-items:center; font-size:16px; z-index:2; display:none; }
</style>
</head>
<body>

<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" alt="EventLink" class="logo-img">
        Event<span style="font-family:'Dancing Script',cursive; font-size:40px; color:#dcb6a3;">Link</span>
    </div>
    <div class="header-profile">
        <a href="profile.php" class="profile-link">
            <div class="profile-circle"><?= strtoupper(substr($customer['full_name'],0,1)) ?></div>
            <div class="profile-name"><?= htmlspecialchars($customer['full_name']) ?></div>
        </a>
    </div>
</header>

<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="customer_dashboard.php" class="sidebar-btn">🏠Dashboard</a></li>
        <li><a href="my_bookings.php" class="sidebar-btn">📅My Bookings</a></li>
        <li><a href="book_service.php" class="sidebar-btn active">🛎️Book Services</a></li>
        <li><a href="catering.php" class="sidebar-btn">📅Catering Services</a></li>
        <li><a href="payments.php" class="sidebar-btn">💳Payments</a></li>
        <li><a href="notifications.php" class="sidebar-btn">🔔Notifications</a></li>
        <li><a href="logout.php" class="sidebar-btn logout-btn">🚪Logout</a></li>
    </ul>
</div>

<div class="container mt-4">

<?php if($selected_theme): ?>
    <h2 class="mb-4">Selected Theme</h2>
    <div class="card mb-4 p-3">
        <h5><?= htmlspecialchars($selected_theme['theme_name']) ?></h5>
        <p><?= htmlspecialchars($selected_theme['description']) ?></p>
        <p><strong>Price:</strong> ₱<?= number_format($selected_theme['price'],2) ?></p>
    </div>

    <h2 class="mb-4">Available Services</h2>
    <?php if(!empty($services)): ?>
    <form method="post" action="book_service.php">
        <div class="mb-3">
            <button type="button" class="btn btn-secondary" id="selectAllBtn">Select All Available</button>
        </div>
        <div class="row">
        <?php foreach($services as $service):
            $available = intval($service['assigned_staff_count']);
        ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="checkmark">✔</div>
                    <div class="card-body">
                        <h5><?= htmlspecialchars($service['service_name']) ?></h5>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                        <?php if(isset($service['rate'])): ?>
                            <p><strong>Rate:</strong> ₱<?= number_format($service['rate'],2) ?></p>
                        <?php endif; ?>
                        <p><strong>Staff Available:</strong> <?= $available ?></p>

                        <button type="button" class="btn btn-select service-select-btn" data-service="<?= $service['service_id'] ?>"
                            <?= $available===0 ? 'disabled style="background:#ccc; cursor:not-allowed;"' : '' ?>>
                            <?= $available===0 ? 'Unavailable':'Select' ?>
                        </button>

                        <input type="checkbox" name="services[]" class="service-checkbox" value="<?= $service['service_id'] ?>" hidden>

                        <div class="inclusion-box mt-3" id="inclusion-box-<?= $service['service_id'] ?>" style="display:none;">
                            <label><strong>Choose Inclusion:</strong></label>
                            <select class="form-select inclusion-select" data-service="<?= $service['service_id'] ?>">
                                <option value="">-- Select Inclusion --</option>
                                <?php if(!empty($inclusionMap[$service['service_id']])): ?>
                                    <?php foreach($inclusionMap[$service['service_id']] as $inc): ?>
                                        <option value="<?= $inc['inclusion_id'] ?>" data-price="<?= $inc['price'] ?>">
                                            <?= htmlspecialchars($inc['inclusion_name']) ?> (₱<?= number_format($inc['price'],2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No inclusions available</option>
                                <?php endif; ?>
                            </select>
                            <p class="mt-2 inclusion-price" id="price-<?= $service['service_id'] ?>" style="display:none;">
                                <strong>Inclusion Price:</strong> ₱<span>0</span>
                            </p>

                            <!-- Schedule inputs -->
                            <div class="schedule-box mt-2" id="schedule-box-<?= $service['service_id'] ?>" style="display:none;">
                                <label><strong>Schedule:</strong></label>
                                <input type="date" class="form-control mb-2 schedule-day" data-service="<?= $service['service_id'] ?>" placeholder="Select day">
                                <input type="time" class="form-control schedule-time" data-service="<?= $service['service_id'] ?>" placeholder="Select time">
                            </div>

                            <input type="hidden" name="selected_inclusion[<?= $service['service_id'] ?>]" id="selected-inclusion-<?= $service['service_id'] ?>">
                            <input type="hidden" name="selected_schedule[<?= $service['service_id'] ?>][day]" id="schedule-day-<?= $service['service_id'] ?>">
                            <input type="hidden" name="selected_schedule[<?= $service['service_id'] ?>][time]" id="schedule-time-<?= $service['service_id'] ?>">
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary">Book Selected Services</button>
        </div>
    </form>
    <?php else: ?>
        <p class="text-danger">No services are currently available.</p>
    <?php endif; ?>

<?php else: ?>
    <h2 class="mb-4">Available Themes</h2>
    <div class="row">
        <?php foreach($themes as $theme): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 p-3">
                    <h5><?= htmlspecialchars($theme['theme_name']) ?></h5>
                    <p><?= htmlspecialchars($theme['description']) ?></p>
                    <p><strong>Price:</strong> ₱<?= number_format($theme['price'],2) ?></p>
                    <form method="post">
                        <input type="hidden" name="theme_id" value="<?= $theme['theme_id'] ?>">
                        <button type="submit" class="btn btn-primary w-100">Select Theme</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Handle service selection & inclusion display
document.addEventListener("DOMContentLoaded", function() {
    const selectAllBtn = document.getElementById('selectAllBtn');
    const checkboxes = document.querySelectorAll('input[name="services[]"]:not(:disabled)');
    const selectButtons = Array.from(checkboxes).map(cb => cb.previousElementSibling);
    let allSelected = false;

    function toggleSelect(button) {
        const checkbox = button.nextElementSibling;
        const card = button.closest('.card');
        const checkmark = card.querySelector('.checkmark');
        const serviceId = checkbox.value;

        checkbox.checked = !checkbox.checked;
        button.classList.toggle('active');

        if (checkbox.checked) {
            button.textContent = 'Selected';
            checkmark.style.display = 'flex'; // Show checkmark
            // Show inclusion box
            const inclusionBox = document.getElementById("inclusion-box-" + serviceId);
            if (inclusionBox) inclusionBox.style.display = 'block';
        } else {
            button.textContent = 'Select';
            checkmark.style.display = 'none'; // Hide checkmark
            // Hide inclusion and schedule inputs
            const inclusionBox = document.getElementById("inclusion-box-" + serviceId);
            if (inclusionBox) {
                inclusionBox.style.display = 'none';
                // Clear hidden inputs for inclusion & schedule
                document.getElementById("selected-inclusion-" + serviceId).value = '';
                document.getElementById("schedule-day-" + serviceId).value = '';
                document.getElementById("schedule-time-" + serviceId).value = '';
            }
        }

        allSelected = Array.from(checkboxes).every(cb => cb.checked);
        selectAllBtn.textContent = allSelected ? 'Deselect All' : 'Select All Available';
    }

    selectButtons.forEach(btn => btn.addEventListener('click', ()=>toggleSelect(btn)));

    selectAllBtn.addEventListener('click', ()=>{
        allSelected = !allSelected;
        checkboxes.forEach((cb,index)=>{
            const btn = selectButtons[index];
            if(cb.checked !== allSelected){
                toggleSelect(btn);
            }
        });
        selectAllBtn.textContent = allSelected ? 'Deselect All' : 'Select All Available';
    });

    // Update inclusion price & hidden input, show schedule when inclusion selected
    document.querySelectorAll(".inclusion-select").forEach(sel=>{
        sel.addEventListener("change", function(){
            let price = this.selectedOptions[0].dataset.price||0;
            let serviceId = this.dataset.service;
            let priceBox = document.getElementById("price-"+serviceId);
            let scheduleBox = document.getElementById("schedule-box-"+serviceId);

            if(this.value){
                priceBox.style.display="block";
                priceBox.querySelector("span").innerText = price;
                scheduleBox.style.display="block"; // Show schedule
            } else {
                priceBox.style.display="none";
                scheduleBox.style.display="none"; // Hide schedule
                document.getElementById("schedule-day-"+serviceId).value = '';
                document.getElementById("schedule-time-"+serviceId).value = '';
            }

            document.getElementById("selected-inclusion-"+serviceId).value = this.value;
        });
    });

    // Update hidden schedule inputs
    document.querySelectorAll(".schedule-day").forEach(input=>{
        input.addEventListener("change", function(){
            let serviceId = this.dataset.service;
            document.getElementById("schedule-day-"+serviceId).value = this.value;
        });
    });
    document.querySelectorAll(".schedule-time").forEach(input=>{
        input.addEventListener("change", function(){
            let serviceId = this.dataset.service;
            document.getElementById("schedule-time-"+serviceId).value = this.value;
        });
    });
});
</script>

</div>
</body>
</html>

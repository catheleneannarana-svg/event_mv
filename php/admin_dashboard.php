<?php
$conn = new PDO("mysql:host=localhost;dbname=event_management", "root", "");

$themes = $conn->query("SELECT COUNT(*) FROM themes")->fetchColumn();
$services = $conn->query("SELECT COUNT(*) FROM services")->fetchColumn();
$customers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$events = $conn->query("SELECT COUNT(*) FROM events")->fetchColumn();
$booking_details = $conn->query("SELECT COUNT(*) FROM booking_details WHERE status != 'confirmed'")->fetchColumn();
$cateringCount = $conn->query("SELECT COUNT(*) FROM catering_users")->fetchColumn();
$venueCount= $conn->query("SELECT COUNT(*) FROM venues")->FetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | EventLink</title>
<link rel="stylesheet" href="../bootstrap/bootstrap.css">
<link rel="stylesheet" href="../fontawesome/css/all.min.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    margin-left: 260px;
    background: #f0f2f5;
    color: #333;
    
}

.dashboard-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 90px;
    background: #ffffff; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
    z-index: 10;
}

.dashboard-header .logo-img {
    width: 100px;
}

.header-profile {
    font-weight: 600;
    color: #555;
}

.sidebar {
    width: 260px;
    height: calc(100vh - 90px);   
    position: fixed;
    top: 90px;
    left: 0;
    background: #2e3b4e;
    padding-top: 30px;
    box-shadow: 2px 0 5px rgba(0,0,0,.1);
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar h2 {
    padding-left: 20px;
    color: #ffb74d; 
    font-size: 28px;
}

.sidebar-nav {
    list-style: none;
    padding: 0 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sidebar-btn {
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    color: #e0e0e0;
    background: #3b4a5c; 
    border-radius: 8px;
    transition: .3s;
}

.sidebar-btn:hover {
    background: #ffb74d;
    color: #1a1a1a;
}

.sidebar-btn.active {
    background: #ffb74d;
    color: #1a1a1a;
    font-weight: 600;
}

.container {
    margin-top: 120px;
    padding: 20px;
}

.box {
    padding: 25px;
    background: #ffffff; 
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    transition: transform 0.2s, background 0.3s;
}

.box:hover {
    transform: translateY(-5px);
    background: #f8f9fa; 
}

.icon {
    font-size: 40px;
    color: #ffb74d; 
}

.box h5 {
    margin-top: 10px;
    font-weight: 600;
    color: #333;
}

.box p {
    font-size: 22px;
    font-weight: bold;
    margin: 0;
    color: #555;
}
.logo{
    font-size: 30px;
    font-weight: 700; 
}

.section-header {
    text-align: left;      
    font-weight: 700;       
    font-size: 32px;        
    color: #333;
    border-left: 6px solid #dcb6a3;
    padding-left: 10px;
    margin-bottom: 20px;
}

</style>
</head>

<body>

<header class="dashboard-header">
    <div class="logo">
        <img src="../img/logo.png" class="logo-img">
        Event<span style="font-family:'Dancing Script',cursive;color:#dcb6a3; font-size:40px;"> Link</span>
    </div>

<div class="header-profile">
    <a href="approved_restaurants.php" style="text-decoration: none; color: inherit;">
        <i class="fa fa-utensils"></i> catering
    </a>
</div>

</header>

<!-- SIDEBAR (exact same design) -->
<div class="sidebar">
    <h2>Menu</h2>
    <ul class="sidebar-nav">
        <li><a href="admin_dashboard.php" class="sidebar-btn active"> Dashboard</a></li>
        <a href="themes.php" class="sidebar-btn">Manage Themes</a>
        <a href="manage_catering.php" class="sidebar-btn"> Manage Catering</a>
        <a href="services.php" class="sidebar-btn">Manage Services</a>
        <a href="venues.php" class="sidebar-btn">Manage Venues</a>
        <a href="customers.php" class="sidebar-btn">Customers</a>
        <a href="events.php" class="sidebar-btn">Events</a>
        <a href="bookings.php" class="sidebar-btn">Bookings</a>
        <a href="admin_notifications.php" class="sidebar-btn"> Notifications</a>
        <li><a href="logout.php" class="sidebar-btn">🚪 Logout</a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="container">
    <h1 class="section-header mb-4">Admin Dashboard</h1>

    <div class="row g-4">

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-palette icon"></i>
                <h5>Total Themes</h5>
                <p><?= $themes ?></p>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-building icon"></i>
                <h5>Total Venue</h5>
                <p><?= $venueCount ?></p>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-cogs icon"></i>
                <h5>Total Services</h5>
                <p><?= $services ?></p>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-users icon"></i>
                <h5>Total Customers</h5>
                <p><?= $customers ?></p>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-calendar-alt icon"></i>
                <h5>Total Events</h5>
                <p><?= $events ?></p>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-book icon"></i>
                <h5>Total Bookings</h5>
                <p><?= $booking_details ?></p>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="box">
                <i class="fas fa-concierge-bell icon"></i>
                <h5>Total Catering</h5>
                <p><?= $cateringCount ?></p>
            </div>
        </div>

    </div>
</div>

</body>
</html>

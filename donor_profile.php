<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect DB
$conn = new mysqli("localhost", "root", "", "blood_management_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check login
if(!isset($_SESSION['donor_phone'])){
    header("Location: donor_login.php");
    exit;
}

$donor_phone = $_SESSION['donor_phone'];
$updateMsg = "";

// Handle profile update
if(isset($_POST['update'])){
    $fullName = $_POST['fullName'];
    $contactNumber = $_POST['contactNumber'];
    $location = $_POST['location'];
    $bloodType = $_POST['bloodType'];
    $age = $_POST['age'];
    $lastDonation = $_POST['lastDonation'] ?: null;

    $stmt = $conn->prepare("UPDATE donors 
        SET fullName=?, contactNumber=?, location=?, bloodType=?, age=?, lastDonation=? 
        WHERE contactNumber=?");
    $stmt->bind_param("ssssiss", $fullName, $contactNumber, $location, $bloodType, $age, $lastDonation, $donor_phone);
    if($stmt->execute()){
        $updateMsg = "Profile updated successfully!";
        $_SESSION['donor_phone'] = $contactNumber; // update session if phone changed
        $donor_phone = $contactNumber;
    } else {
        $updateMsg = "Update failed. Try again!";
    }
    $stmt->close();
}

// Fetch donor info
$stmt = $conn->prepare("SELECT * FROM donors WHERE contactNumber=?");
$stmt->bind_param("s", $donor_phone);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

// Fetch notifications for this donor
$notifications = [];
$stmt = $conn->prepare("SELECT * FROM donor_notifications WHERE donor_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $donor['id']); // use donor id here
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $notifications[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Profile</title>
<style>
body{background:#f5f5f5;font-family:'Segoe UI',sans-serif;color:#333;}
.container{width:90%; max-width:700px;margin:20px auto;}
header{background:linear-gradient(135deg,#1976d2,#0d47a1);color:#fff;padding:1rem;text-align:center;border-radius:10px;margin-bottom:20px;}
.form-box{background:#fff;padding:2rem;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:20px;}
.form-group{margin-bottom:1rem;}
.form-group label{display:block;margin-bottom:0.5rem;font-weight:600;}
.form-group input, .form-group select{width:100%;padding:10px;border:1px solid #ddd;border-radius:5px;}
.btn{width:100%;padding:10px;border:none;border-radius:50px;background:#d32f2f;color:#fff;font-weight:600;cursor:pointer;transition:0.3s;}
.btn:hover{background:#b71c1c;}
.message{margin-bottom:1rem;padding:10px;border-radius:5px;background:#e8f5e9;color:#2e7d32;}
.navbar{text-align:right;margin-bottom:10px;}
.navbar a{color:#fff;background:#1976d2;padding:8px 15px;border-radius:50px;text-decoration:none;margin-left:10px;}
.navbar a:hover{background:#0d47a1;}
.notification{background:#e8f5e9;color:#2e7d32;padding:10px 15px;border-radius:5px;margin-bottom:10px;}
.notification .time{float:right;font-size:0.8rem;color:#555;}
</style>
</head>
<body>

<header>
    <h1>Blood Management System - Donor Profile</h1>
    <div class="navbar">
        <a href="donor_logout.php">Logout</a>
        <a href="index.html">Home</a>
    </div>
</header>

<div class="container">
    <div class="form-box">
        <h2>My Profile</h2>
        <?php if($updateMsg != '') echo "<div class='message'>$updateMsg</div>"; ?>
        <form method="POST">
            <input type="hidden" name="update" value="1">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="fullName" value="<?= htmlspecialchars($donor['fullName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <input type="tel" name="contactNumber" value="<?= htmlspecialchars($donor['contactNumber']); ?>" required>
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($donor['location']); ?>" required>
            </div>
            <div class="form-group">
                <label>Blood Type:</label>
                <select name="bloodType" required>
                    <?php
                    $types = ["A+","A-","B+","B-","O+","O-","AB+","AB-"];
                    foreach($types as $type){
                        $sel = $donor['bloodType']==$type?"selected":"";
                        echo "<option value='$type' $sel>$type</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Age:</label>
                <input type="number" name="age" min="18" max="65" value="<?= $donor['age']; ?>" required>
            </div>
            <div class="form-group">
                <label>Last Donation Date:</label>
                <input type="date" name="lastDonation" value="<?= $donor['lastDonation']; ?>">
            </div>
            <button type="submit" class="btn">Update Profile</button>
        </form>
    </div>

    <div class="form-box">
        <h2>Notifications</h2>
        <?php if(count($notifications) > 0): ?>
            <?php foreach($notifications as $note): ?>
                <div class="notification">
                    <i class="fas fa-bell"></i> <?= htmlspecialchars($note['message']); ?>
                    <span class="time"><?= date("d M Y H:i", strtotime($note['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No notifications yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

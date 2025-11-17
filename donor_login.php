<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "blood_management_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$loginMsg = "";

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $phone = trim($_POST['loginPhone']);

    // Check donor
    $stmt = $conn->prepare("SELECT * FROM donors WHERE contactNumber = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $donor = $result->fetch_assoc();

        // Save donor ID and phone in session
        $_SESSION['donor_id'] = $donor['id'];
        $_SESSION['donor_phone'] = $donor['contactNumber'];

        // Redirect to donor profile
        header("Location: donor_profile.php");
        exit();
    } else {
        $loginMsg = "Phone number not found. Please register first.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donor Login - Blood Management System</title>
<style>
* {margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif;}
body {background:#f5f5f5; color:#333;}
.container {width:90%; max-width:500px; margin:50px auto;}
header {background:linear-gradient(135deg,#1976d2,#0d47a1); color:#fff; padding:1rem; text-align:center; border-radius:10px;}
.form-box {background:#fff; padding:2rem; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.form-group {margin-bottom:1rem;}
.form-group label {display:block; margin-bottom:0.5rem; font-weight:600;}
.form-group input {width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;}
.btn {width:100%; padding:10px; border:none; border-radius:50px; background:#d32f2f; color:#fff; font-weight:600; cursor:pointer; transition:0.3s;}
.btn:hover {background:#b71c1c;}
.message {margin-bottom:1rem; padding:10px; border-radius:5px; background:#ffebee; color:#c62828;}
.navbar {margin:15px 0; text-align:center;}
.navbar a {color:#1976d2; text-decoration:none; font-weight:600; margin:0 10px;}
.navbar a:hover {text-decoration:underline;}
</style>
</head>
<body>

<header>
    <h1>Blood Management System</h1>
    <p>Donor Login</p>
</header>

<div class="container">
    <div class="navbar">
        <a href="index.html">Home</a> | 
        <a href="donor.php">Register</a>
    </div>

    <div class="form-box">
        <h2>Login as Donor</h2>
        <?php if ($loginMsg != '') echo "<div class='message'>$loginMsg</div>"; ?>
        <form method="POST">
            <input type="hidden" name="login" value="1">
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="tel" name="loginPhone" required placeholder="Enter your registered phone number">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</div>

</body>
</html>

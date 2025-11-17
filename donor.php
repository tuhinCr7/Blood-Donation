
<?php
session_start();

// Connect to the database
$conn = new mysqli("localhost", "root", "", "blood_management_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$registerMsg = "";

// Handle registration form submission
if (isset($_POST['register'])) {
    $fullName = trim($_POST['fullName']);
    $contactNumber = trim($_POST['contactNumber']);
    $location = trim($_POST['location']);
    $bloodType = trim($_POST['bloodType']);
    $age = intval($_POST['age']); // ensure numeric
    $lastDonation = !empty($_POST['lastDonation']) ? $_POST['lastDonation'] : NULL;

    // Check if the phone number is already registered
    $check = $conn->prepare("SELECT id FROM donors WHERE contactNumber = ?");
    if (!$check) die("Prepare failed: " . $conn->error);
    $check->bind_param("s", $contactNumber);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $registerMsg = "❌ Phone number already registered. Please use another.";
    } else {
        // Insert donor information
        $stmt = $conn->prepare("INSERT INTO donors (fullName, contactNumber, location, bloodType, age, lastDonation) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);

        // "sssis" → fullName (s), contactNumber (s), location (s), bloodType (s), age (i), lastDonation (s or null)
        $stmt->bind_param("ssssis", $fullName, $contactNumber, $location, $bloodType, $age, $lastDonation);

        if ($stmt->execute()) {
            $registerMsg = "✅ Registration successful! Your information has been saved.";
        } else {
            $registerMsg = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donor Registration</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f0f4f8;color:#333;}
.container{width:90%; max-width:700px; margin:50px auto;}
header{background:linear-gradient(135deg,#ff5252,#ff1744); color:#fff; padding:1.5rem; text-align:center; border-radius:15px; box-shadow:0 5px 15px rgba(0,0,0,0.2);}
h1{margin-bottom:0;}
.form-box{background:#fff;padding:2rem;border-radius:15px;box-shadow:0 8px 20px rgba(0,0,0,0.15);margin-top:20px;transition:0.3s;}
.form-box:hover{transform:translateY(-5px);}
h2{color:#ff1744;margin-bottom:1rem;text-align:center;}
.form-group{margin-bottom:1rem;}
.form-group label{display:block;margin-bottom:0.5rem;font-weight:600;color:#444;}
.form-group input, .form-group select{width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; font-size:1rem; transition:0.3s;}
.form-group input:focus, .form-group select:focus{border-color:#ff5252; outline:none; box-shadow:0 0 8px rgba(255,82,82,0.3);}
.btn{width:100%;padding:12px;border:none;border-radius:50px;background:#ff5252;color:#fff;font-weight:600;font-size:1.1rem;cursor:pointer;transition:0.3s;}
.btn:hover{background:#ff1744;}
.message{margin-bottom:1rem;padding:12px;border-radius:10px;text-align:center;font-weight:600;}
.message.success{background:#e8f5e9;color:#2e7d32;}
.message.error{background:#ffebee;color:#c62828;}
.back-btn{display:inline-block;margin-bottom:20px;color:#ff5252;padding:10px 20px;border:2px solid #ff5252;border-radius:50px;text-decoration:none;font-weight:600;transition:0.3s;}
.back-btn:hover{background:#ff5252;color:#fff;}
</style>
</head>
<body>

<header>
    <h1>Blood Management System</h1>
    <p>Donor Registration</p>
</header>

<div class="container">
    <div style="text-align:center; margin-bottom:20px;">
        <a href="index.html" class="back-btn"><i class="fas fa-home"></i> Home</a>
        <a href="donor_login.php" class="back-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>

    <div class="form-box">
        <h2>Register as a Donor</h2>
        <?php 
        if($registerMsg != ''){
            $class = strpos($registerMsg, '✅') !== false ? 'success' : 'error';
            echo "<div class='message $class'>$registerMsg</div>";
        }
        ?>
        <form method="POST">
            <input type="hidden" name="register" value="1">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullName" required placeholder="Enter your full name">
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="tel" name="contactNumber" required placeholder="Enter your phone number">
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" required placeholder="Enter your city or area">
            </div>
            <div class="form-group">
                <label>Blood Type</label>
                <select name="bloodType" required>
                    <option value="">Select Your Blood Type</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" min="18" max="65" required placeholder="Enter your age">
            </div>
            <div class="form-group">
                <label>Last Donation Date</label>
                <input type="date" name="lastDonation">
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>
</div>

</body>
</html>

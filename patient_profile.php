<?php
session_start();
include "db_connect.php";

// Check if patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: patient_login.html");
    exit();
}

$id = $_SESSION['patient_id'];

// Fetch patient information
$sql = "SELECT * FROM patients WHERE id='$id'";
$result = $conn->query($sql);
$patient = $result->fetch_assoc();
$patient_id = $_SESSION['patient_id']; // Assuming you store patient session on login
$notif_stmt = $conn->prepare("SELECT * FROM notifications WHERE patient_id=? ORDER BY created_at DESC");
$notif_stmt->bind_param("i", $patient_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();
$notif_stmt->close();


// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $fullName = $_POST['fullName'];
    $age = $_POST['age'];
    $contactNumber = $_POST['contactNumber'];
    $address = $_POST['address'];
    $medicalCondition = $_POST['medicalCondition'];

    $update = "UPDATE patients SET fullName='$fullName', age='$age', contactNumber='$contactNumber',
                address='$address', medicalCondition='$medicalCondition' WHERE id='$id'";

    if ($conn->query($update) === TRUE) {
        header("Location: patient_profile.php?msg=updated");
        exit();
    } else {
        echo "Update Failed!";
    }
}

// Handle blood request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitRequest'])) {
    $patientId = intval($_POST['patientId']);
    $bloodType = $_POST['bloodType'];
    $urgency = $_POST['urgency'];
    $requestTime = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO requests (patient_id, blood_type, urgency, request_time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $patientId, $bloodType, $urgency, $requestTime);

    if($stmt->execute()){
        $requestMsg = "<div style='background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:5px; margin-bottom:20px; text-align:center;'>
                        <i class='fas fa-check-circle'></i> Blood request submitted successfully!
                      </div>";
    } else {
        $requestMsg = "<div style='background:#ffebee; color:#c62828; padding:15px; border-radius:5px; margin-bottom:20px; text-align:center;'>
                        <i class='fas fa-times-circle'></i> Failed to submit blood request!
                      </div>";
    }
    $stmt->close();
}

// Fetch last 5 notifications for this patient
$notificationsResult = $conn->query("SELECT * FROM patient_notifications WHERE patient_id='$id' ORDER BY created_at DESC LIMIT 5");
$notifications = [];
if($notificationsResult->num_rows > 0){
    while($row = $notificationsResult->fetch_assoc()){
        $notifications[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Profile - Blood Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
    :root { --primary-red:#d32f2f; --dark-red:#b71c1c; --primary-blue:#1976d2; --dark-blue:#0d47a1; --light-gray:#f5f5f5; --text-dark:#333; --text-light:#fff; --shadow:0 4px 6px rgba(0,0,0,0.1);}
    body {background-color: var(--light-gray); color: var(--text-dark);}
    .container {width:90%; max-width:1200px; margin:20px auto;}
    header {background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue)); color:var(--text-light); padding:1rem; text-align:center; box-shadow: var(--shadow);}
    .info-box, .form-box {background:#fff; padding:20px; border-radius:10px; box-shadow: var(--shadow); margin-bottom:20px;}
    .info-box h2, .form-box h2 {color: var(--dark-blue); margin-bottom:15px;}
    .info-box table {width:100%; border-collapse: collapse;}
    .info-box table td {padding:10px; border-bottom:1px solid #ddd;}
    .form-group {margin-bottom:15px;}
    .form-group label {display:block; margin-bottom:5px; font-weight:600;}
    .form-group input, .form-group textarea, .form-group select {width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;}
    .btn {background-color: var(--primary-red); color:#fff; border:none; padding:10px 20px; border-radius:50px; cursor:pointer; transition:0.3s; width:100%;}
    .btn:hover {background-color: var(--dark-red);}
    .home-btn {background-color: var(--primary-blue); margin-bottom:20px;}
    .home-btn:hover {background-color: var(--dark-blue);}
</style>
</head>
<body>

<header>
    <h1>Patient Profile - Blood Management System</h1>
</header>

<div class="container">

    <a href="index.html" class="btn home-btn"><i class="fas fa-home"></i> Home</a>

    <?php if(isset($_GET['msg']) && $_GET['msg']=='updated'): ?>
        <div style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:5px; margin-bottom:20px; text-align:center;">
            <i class="fas fa-check-circle"></i> Information updated successfully!
        </div>
    <?php endif; ?>

    <?php if(isset($requestMsg)) echo $requestMsg; ?>

    <!-- Notifications -->
    <?php if(count($notifications) > 0): ?>
        <div class="form-box">
            <h2>Recent Notifications</h2>
            <?php foreach($notifications as $note): ?>
                <div style="background:<?= strpos($note['message'],'Sorry')!==false?'#ffebee':'#e8f5e9' ?>; 
                            color:<?= strpos($note['message'],'Sorry')!==false?'#c62828':'#2e7d32' ?>; 
                            padding:10px 15px; border-radius:5px; margin-bottom:10px;">
                    <i class="fas <?= strpos($note['message'],'Sorry')!==false?'fa-times-circle':'fa-check-circle' ?>"></i> 
                    <?= htmlspecialchars($note['message']); ?>
                    <span style="float:right; font-size:0.8rem; color:#555;"><?= date("d M Y H:i", strtotime($note['created_at'])); ?></span>
                </div>
                
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    

    <!-- Patient Info -->
    <div class="info-box">
        <h2>Your Information</h2>
        <table>
            <tr><td><strong>Full Name:</strong></td><td><?= $patient['fullName']; ?></td></tr>
            <tr><td><strong>Age:</strong></td><td><?= $patient['age']; ?></td></tr>
            <tr><td><strong>Contact Number:</strong></td><td><?= $patient['contactNumber']; ?></td></tr>
            <tr><td><strong>Address:</strong></td><td><?= $patient['address']; ?></td></tr>
            <tr><td><strong>Medical Info:</strong></td><td><?= $patient['medicalCondition']; ?></td></tr>
        </table>
    </div>

    <!-- Update Info Form -->
    <div class="form-box">
        <h2>Update Your Information</h2>
        <form method="POST">
            <input type="hidden" name="update_info" value="1">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="fullName" value="<?= $patient['fullName']; ?>" required>
            </div>
            <div class="form-group">
                <label>Age:</label>
                <input type="number" name="age" value="<?= $patient['age']; ?>" required>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <input type="text" name="contactNumber" value="<?= $patient['contactNumber']; ?>" required>
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" name="address" value="<?= $patient['address']; ?>" required>
            </div>
            <div class="form-group">
                <label>Medical Information:</label>
                <textarea name="medicalCondition" required><?= $patient['medicalCondition']; ?></textarea>
            </div>
            <button type="submit" class="btn">Update Information</button>
        </form>
    </div>

    <!-- Blood Request Form -->
    <div class="form-box">
        <h2>Request Blood</h2>
        <form action="" method="POST">
            <input type="hidden" name="patientId" value="<?= $id; ?>">
            <div class="form-group">
                <label>Select Blood Type:</label>
                <select name="bloodType" required>
                    <option value="">--Select Blood Type--</option>
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
                <label>Urgency Level:</label>
                <select name="urgency" required>
                    <option value="">--Select Urgency--</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>
            <button type="submit" name="submitRequest" class="btn">Submit Blood Request</button>
        </form>
    </div>

</div>
</body>
</html>


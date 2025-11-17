<?php
// register_patient.php

// Database connection settings
$servername = "localhost";
$username = "root"; // আপনার ডাটাবেস ইউজারনেম
$password = "";     // আপনার ডাটাবেস পাসওয়ার্ড
$dbname = "blood_management_db"; // আপনার ডাটাবেস নাম

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data safely
$fullName = $conn->real_escape_string($_POST['fullName']);
$age = isset($_POST['age']) ? intval($_POST['age']) : NULL;
$contactNumber = $conn->real_escape_string($_POST['contactNumber']);
$address = $conn->real_escape_string($_POST['address']);
$medicalCondition = $conn->real_escape_string($_POST['medicalCondition']);

// Insert into patients table
$sql = "INSERT INTO patients (fullName, age, contactNumber, address, medicalCondition) 
        VALUES ('$fullName', $age, '$contactNumber', '$address', '$medicalCondition')";

if ($conn->query($sql) === TRUE) {
    // Get last inserted patient ID
    $patient_id = $conn->insert_id;

    // Redirect to patient profile page with ID
    header("Location: patient_profile.php?id=" . $patient_id);
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>

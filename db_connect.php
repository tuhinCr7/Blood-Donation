<?php
$host = "localhost";
$user = "root";  // আপনার MySQL username
$pass = "";      // আপনার MySQL password
$db   = "blood_management_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>

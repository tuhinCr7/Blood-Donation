<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contactNumber = $_POST['phoneNumber'];

    $sql = "SELECT * FROM patients WHERE contactNumber='$contactNumber'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        session_start();
        $_SESSION['patient_id'] = $row['id'];
        header("Location: patient_profile.php");
        exit();
    } else {
        echo "Invalid Phone Number!";
    }
}
?>

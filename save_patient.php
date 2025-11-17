<?php
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $age = $_POST['age'];
    $contactNumber = $_POST['contactNumber'];
    $address = $_POST['address'];
    $medicalCondition = $_POST['medicalCondition'];

    $sql = "INSERT INTO patients (fullName, age, contactNumber, address, medicalCondition)
            VALUES ('$fullName', '$age', '$contactNumber', '$address', '$medicalCondition')";

    if ($conn->query($sql) === TRUE) {
        header("Location: patient_login.html?msg=registered");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

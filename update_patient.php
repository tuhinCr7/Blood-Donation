<?php
include "db_connection.php";

if($_SERVER['REQUEST_METHOD']=="POST"){
    $contact = $_POST['contactNumber'];
    $name = $_POST['fullName'];
    $age = $_POST['age'];
    $address = $_POST['address'];
    $med = $_POST['medicalCondition'];

    $sql = "UPDATE patients SET fullName='$name', age='$age', address='$address', medicalCondition='$med' WHERE contactNumber='$contact'";
    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Profile updated successfully'); window.location.href='patient_profil.php';</script>";
    }else{
        echo "Error: ".mysqli_error($conn);
    }
}
?>

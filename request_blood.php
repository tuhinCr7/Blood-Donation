<?php
include "db_connection.php";

if($_SERVER['REQUEST_METHOD']=="POST"){
    $patient_id = $_POST['patient_id'];
    $blood_type = $_POST['blood_type'];
    $urgency = $_POST['urgency'];

    $sql = "INSERT INTO blood_requests (patient_id, blood_type, urgency, status) VALUES ('$patient_id','$blood_type','$urgency','pending')";
    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Blood request submitted!'); window.location.href='patient_profil.php';</script>";
    }else{
        echo "Error: ".mysqli_error($conn);
    }
}
?>

<?php
include "db_connect.php";

if(isset($_POST['id'])){
    $requestId = intval($_POST['id']);

    // Get patient id from request
    $res = $conn->query("SELECT patient_id FROM requests WHERE id='$requestId'");
    if($res && $res->num_rows>0){
        $row = $res->fetch_assoc();
        $patient_id = $row['patient_id'];

        // Insert negative notification
        $message = "Sorry, we currently don't have the required blood for your request.";
        $conn->query("INSERT INTO patient_notifications (patient_id, message) VALUES ('$patient_id', '$message')");

        // Delete request
        $conn->query("DELETE FROM requests WHERE id='$requestId'");
        echo "success";
    } else {
        echo "error";
    }
}
?>

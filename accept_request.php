<?php
include "db_connect.php";

if(isset($_POST['id'])){
    $requestId = intval($_POST['id']);

    // Get patient id from request
    $res = $conn->query("SELECT patient_id FROM requests WHERE id='$requestId'");
    if($res && $res->num_rows>0){
        $row = $res->fetch_assoc();
        $patient_id = $row['patient_id'];

        // Insert positive notification
        $message = "Your blood request has been accepted. Please contact us  +88 02 58052313, +88 02 58052314.";
        $conn->query("INSERT INTO patient_notifications (patient_id, message) VALUES ('$patient_id', '$message')");

        // Update request status
        $conn->query("UPDATE requests SET status='Accepted' WHERE id='$requestId'");
        echo "success";
    } else {
        echo "error";
    }
}
?>

<?php
session_start();
include "db_connect.php";

if(isset($_POST['blood_type'], $_POST['message'])){
    $bloodType = $_POST['blood_type'];
    $message = trim($_POST['message']);

    // Get all donors with that blood type
    $stmt = $conn->prepare("SELECT id FROM donors WHERE bloodType=?");
    $stmt->bind_param("s", $bloodType);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 0){
        echo json_encode(["status"=>"error","msg"=>"No donors found with this blood type"]);
        exit;
    }

    // Insert notification for each donor
    $insert = $conn->prepare("INSERT INTO donor_notifications (donor_id, message) VALUES (?, ?)");
    while($row = $result->fetch_assoc()){
        $insert->bind_param("is", $row['id'], $message);
        $insert->execute();
    }

    echo json_encode(["status"=>"success","msg"=>"Notification sent to all $bloodType donors"]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Invalid request"]);
}
?>

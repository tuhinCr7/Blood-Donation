<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "blood_management_db");
if ($conn->connect_error) {
    echo json_encode(['success'=>false,'message'=>'Database connection failed']);
    exit;
}

// Get POST data
$donor_id = isset($_POST['donor_id']) ? intval($_POST['donor_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($donor_id <= 0 || !in_array($status,['regular','irregular'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid input']);
    exit;
}

// Update donor status
$stmt = $conn->prepare("UPDATE donors SET donor_status=? WHERE id=?");
if (!$stmt) {
    echo json_encode(['success'=>false,'message'=>$conn->error]);
    exit;
}
$stmt->bind_param("si", $status, $donor_id);
if($stmt->execute()){
    echo json_encode(['success'=>true,'message'=>'Donor status updated successfully']);
} else {
    echo json_encode(['success'=>false,'message'=>'Failed to update donor status']);
}
$stmt->close();
$conn->close();

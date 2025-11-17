<?php
$conn = new mysqli("localhost", "root", "", "blood_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Using GET method
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM patients WHERE id = $id";
    if($conn->query($sql) === TRUE){
        header("Location: admin_profil.php"); // redirect back
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Or using POST method
/*
if(isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM patients WHERE id = $id";
    if($conn->query($sql) === TRUE){
        header("Location: admin_profil.php"); // redirect back
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}
*/

$conn->close();
?>

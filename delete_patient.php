<?php
include "db_connect.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM patients WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_manage_patients.php?msg=deleted");
    } else {
        echo "Delete Failed!";
    }
}
?>

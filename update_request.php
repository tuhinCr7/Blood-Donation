<?php
$conn = new mysqli("localhost","root","","blood_management_db");
if($conn->connect_error){ die("Connection failed: ".$conn->connect_error); }

if(isset($_POST['id'], $_POST['field'], $_POST['value'])){
    $id = intval($_POST['id']);
    $field = $_POST['field'];
    $value = $_POST['value'];

    if(in_array($field, ['blood_type','urgency'])){
        $stmt = $conn->prepare("UPDATE requests SET $field=? WHERE id=?");
        $stmt->bind_param("si",$value,$id);
        $stmt->execute();
        $stmt->close();
    }
}
$conn->close();
?>

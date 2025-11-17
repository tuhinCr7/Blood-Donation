<?php
include "db_connect.php";

$sql = "SELECT * FROM patients";
$result = $conn->query($sql);
?>

<h2>All Registered Patients</h2>
<table border="1">
    <tr>
        <th>ID</th><th>Name</th><th>Age</th><th>Phone</th><th>Address</th><th>Condition</th><th>Action</th>
    </tr>
    <?php while($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= $row['id']; ?></td>
        <td><?= $row['fullName']; ?></td>
        <td><?= $row['age']; ?></td>
        <td><?= $row['contactNumber']; ?></td>
        <td><?= $row['address']; ?></td>
        <td><?= $row['medicalCondition']; ?></td>
        <td><a href="delete_patient.php?id=<?= $row['id']; ?>">Delete</a></td>
    </tr>
    <?php } ?>
</table>

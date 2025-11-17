<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "blood_management_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if donor status column exists, if not, add it
$checkColumn = $conn->query("SHOW COLUMNS FROM donors LIKE 'donor_status'");
if ($checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE donors ADD COLUMN donor_status ENUM('regular', 'irregular') DEFAULT 'irregular'");
}

// Fetch patients
$patients = $conn->query("SELECT * FROM patients");

// Fetch donors with status
$donors = $conn->query("SELECT * FROM donors");

// Fetch requests with patient info
$requests = $conn->query("SELECT r.*, p.fullName FROM requests r 
                          LEFT JOIN patients p ON r.patient_id = p.id 
                          ORDER BY r.request_time DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Profile - Blood Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
:root { 
    --primary-red:#d32f2f; --dark-red:#b71c1c; 
    --primary-blue:#1976d2; --dark-blue:#0d47a1; 
    --light-gray:#f5f5f5; --text-dark:#333; --text-light:#fff;
    --shadow:0 4px 6px rgba(0,0,0,0.1);
    --regular-color:#388e3c; --irregular-color:#f57c00;
}
body { background-color: var(--light-gray); color: var(--text-dark); line-height:1.6;}
.container { width:100%; max-width:1400px; margin:0 auto; padding:0 20px;}
header { 
    background: linear-gradient(135deg,var(--primary-blue) 0%,var(--dark-blue)100%); 
    color:var(--text-light); padding:1rem 0; position:sticky; top:0; z-index:100; box-shadow:var(--shadow);
}
.header-content{display:flex; justify-content:space-between; align-items:center;}
.logo{display:flex; align-items:center; gap:10px;}
.logo i{font-size:2rem;color:#ff5252;}
.logo h1{font-size:1.8rem;font-weight:700;}
.logo span{color:#ff5252;}
.nav-menu{display:flex; list-style:none; gap:1.5rem;}
.nav-item a{color:var(--text-light); text-decoration:none; font-weight:500; padding:0.5rem 1rem; border-radius:4px; transition: all 0.3s ease;}
.nav-item a:hover, .nav-item a.active{background-color:rgba(255,255,255,0.2);}
.admin-profile{padding:2rem 0;}
.welcome-section{
    background:white; border-radius:10px; padding:2rem; margin-bottom:2rem; 
    box-shadow:var(--shadow); display:flex; justify-content:space-between; align-items:center;
}
.welcome-text h2{color:var(--dark-blue); margin-bottom:0.5rem;}
.tabs{background:white; border-radius:10px; overflow:hidden; box-shadow:var(--shadow); margin-bottom:2rem;}
.tab-header{display:flex; background:var(--light-gray); border-bottom:1px solid #ddd;}
.tab-link{padding:1rem 1.5rem; cursor:pointer; transition:all 0.3s ease; font-weight:600;}
.tab-link.active{background:white; border-bottom:3px solid var(--primary-red);}
.tab-content{padding:0; display:none;}
.tab-content.active{display:block; padding:1.5rem;}
.data-table{width:100%; border-collapse:collapse; margin-bottom:1.5rem;}
.data-table th, .data-table td{padding:0.8rem; text-align:left; border-bottom:1px solid #ddd;}
.data-table th{background-color:var(--light-gray); font-weight:600;}
.data-table tr:hover{background-color:#f9f9f9;}
.btn{
    padding:0.5rem 1rem; border-radius:4px; font-weight:600; text-decoration:none; 
    border:none; cursor:pointer; font-size:0.9rem; display:inline-block; transition:all 0.3s ease;
}
.btn-primary{background-color:var(--primary-blue); color:var(--text-light);}
.btn-primary:hover{background-color:var(--dark-blue);}
.btn-danger{background-color:var(--primary-red); color:var(--text-light);}
.btn-danger:hover{background-color:var(--dark-red);}
.btn-success{background-color:var(--regular-color); color:var(--text-light);}
.btn-success:hover{background-color:#2c6b2f;}
.btn-warning{background-color:var(--irregular-color); color:var(--text-light);}
.btn-warning:hover{background-color:#e65100;}
.btn-back{background-color:#555; color:var(--text-light);}
.btn-back:hover{background-color:#333;}
.btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
.search-input {padding:0.4rem 0.6rem; border-radius:4px; border:1px solid #ccc; margin-bottom:10px; width:200px;}
.blood-type-select {padding:0.4rem 0.6rem; border-radius:4px; border:1px solid #ccc; margin-right:10px;}
.status-badge {
    padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
}
.status-regular { background-color: #e8f5e9; color: var(--regular-color); }
.status-irregular { background-color: #fff3e0; color: var(--irregular-color); }
.blood-filter.active { background-color: var(--dark-blue) !important; border: 2px solid var(--primary-blue); }
</style>
</head>
<body>

<header>
    <div class="container header-content">
        <div class="logo"><i class="fas fa-tint"></i><h1>Blood<span>Manager</span></h1></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="#" class="active">Home</a></li>
            <li class="nav-item"><a href="#patients">Patients</a></li>
            <li class="nav-item"><a href="#donors">Donors</a></li>
            <li class="nav-item"><a href="#requests">Requests</a></li>
        </ul>
    </div>
</header>

<section class="container admin-profile">
    <div class="welcome-section">
        <div class="welcome-text"><h2>Welcome, Admin</h2><p>Manage all blood-related data efficiently.</p></div>
        <button class="btn btn-back" onclick="location.href='index.html'">Back to Home</button>
    </div>

    <div class="tabs">
        <div class="tab-header">
            <div class="tab-link active" data-tab="patients">Patients</div>
            <div class="tab-link" data-tab="donors">Donors</div>
            <div class="tab-link" data-tab="requests">Requests</div>
        </div>

        <!-- Patients Table -->
        <div id="patients" class="tab-content active">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Full Name</th><th>Age</th><th>Contact Number</th><th>Address</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php
                if ($patients->num_rows > 0) {
                    while($patient = $patients->fetch_assoc()){
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($patient['id'])."</td>";
                        echo "<td>".htmlspecialchars($patient['fullName'])."</td>";
                        echo "<td>".htmlspecialchars($patient['age'])."</td>";
                        echo "<td>".htmlspecialchars($patient['contactNumber'])."</td>";
                        echo "<td>".htmlspecialchars($patient['address'])."</td>";
                        echo "<td><a href='remove_patient.php?id=".$patient['id']."' class='btn btn-danger btn-sm' onclick=\"return confirm('Are you sure to remove this patient?');\">Remove</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No patients found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Donors Table -->
        <div id="donors" class="tab-content">
            <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <?php
                    $bloodTypes = ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"];
                    foreach($bloodTypes as $bt){
                        echo "<button class='btn btn-primary btn-sm blood-filter' data-blood='".trim($bt)."' style='margin-right:5px;'>$bt</button>";
                    }
                    echo "<button class='btn btn-danger btn-sm blood-filter' data-blood='all'>All</button>";
                    ?>
                </div>
                <div>
                    <select id="notify-blood" class="blood-type-select">
                        <option value="">Select Blood Type</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                    </select>
                    <button class="btn btn-primary btn-sm" id="sendNotification">Send Notification</button>
                </div>
            </div>
            <table class="data-table" id="donor-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Contact Number</th>
                        <th>Location</th>
                        <th>Blood Type</th>
                        <th>Last Donation</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($donors->num_rows > 0) {
                    while($donor = $donors->fetch_assoc()){
                        $bt = trim(strtoupper($donor['bloodType']));
                        $status = isset($donor['donor_status']) ? $donor['donor_status'] : 'irregular';
                        echo "<tr data-blood='$bt'>";
                        echo "<td>".htmlspecialchars($donor['id'])."</td>";
                        echo "<td>".htmlspecialchars($donor['fullName'])."</td>";
                        echo "<td>".htmlspecialchars($donor['age'])."</td>";
                        echo "<td>".htmlspecialchars($donor['contactNumber'])."</td>";
                        echo "<td>".htmlspecialchars($donor['location'])."</td>";
                        echo "<td>".htmlspecialchars($bt)."</td>";
                        
                        echo "<td>".(!empty($donor['lastDonation']) ? htmlspecialchars($donor['lastDonation']) : 'N/A')."</td>";
                        echo "<td class='status-cell'><span class='status-badge status-$status'>".ucfirst($status)."</span></td>";
                        echo "<td>";
                        if($status == 'irregular') {
                            echo "<button class='btn btn-success btn-sm make-regular' data-id='".$donor['id']."'>Make Regular</button>";
                        } else {
                            echo "<button class='btn btn-warning btn-sm make-irregular' data-id='".$donor['id']."'>Make Irregular</button>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align:center;'>No donors found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <!-- Requests Table -->
        <div id="requests" class="tab-content">
            
            <h3>Total Requests: <span id="request-count"><?= $requests->num_rows ?></span></h3>
            <div id="request-list">
            <?php
            if($requests->num_rows > 0){
                echo "<table class='data-table'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Blood Type</th>
                                <th>Urgency</th>
                                <th>Request Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
                while($row = $requests->fetch_assoc()){
                    echo "<tr>
                            <td>".$row['id']."</td>
                            <td>".htmlspecialchars($row['fullName'])."</td>
                            <td>
                                <select class='blood-type' data-id='".$row['id']."'>
                                    <option value='A+' ".($row['blood_type']=='A+'?'selected':'').">A+</option>
                                    <option value='A-' ".($row['blood_type']=='A-'?'selected':'').">A-</option>
                                    <option value='B+' ".($row['blood_type']=='B+'?'selected':'').">B+</option>
                                    <option value='B-' ".($row['blood_type']=='B-'?'selected':'').">B-</option>
                                    <option value='O+' ".($row['blood_type']=='O+'?'selected':'').">O+</option>
                                    <option value='O-' ".($row['blood_type']=='O-'?'selected':'').">O-</option>
                                    <option value='AB+' ".($row['blood_type']=='AB+'?'selected':'').">AB+</option>
                                    <option value='AB-' ".($row['blood_type']=='AB-'?'selected':'').">AB-</option>
                                </select>
                            </td>
                            <td>
                                <select class='urgency' data-id='".$row['id']."'>
                                    <option value='Low' ".($row['urgency']=='Low'?'selected':'').">Low</option>
                                    <option value='Medium' ".($row['urgency']=='Medium'?'selected':'').">Medium</option>
                                    <option value='High' ".($row['urgency']=='High'?'selected':'').">High</option>
                                    <option value='Critical' ".($row['urgency']=='Critical'?'selected':'').">Critical</option>
                                </select>
                            </td>
                            <td>".$row['request_time']."</td>
                            <td>
                                <button class='btn btn-primary btn-sm accept-request' data-id='".$row['id']."'>Accept</button>
                                <button class='btn btn-danger btn-sm remove-request' data-id='".$row['id']."'>Remove</button>
                            </td>
                        </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No requests submitted yet.</p>";
            }
            ?>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Tab switching functionality
const tabLinks = document.querySelectorAll(".tab-link");
const tabContents = document.querySelectorAll(".tab-content");
tabLinks.forEach(link => {
    link.addEventListener("click", () => {
        tabLinks.forEach(l => l.classList.remove("active"));
        link.classList.add("active");
        const target = link.getAttribute("data-tab");
        tabContents.forEach(tc => { tc.classList.toggle("active", tc.id===target); });
    });
});

$(document).ready(function(){
    // Handle making a donor regular
    $(document).on('click', '.make-regular', function() {
        var donorId = $(this).data('id');
        updateDonorStatus(donorId, 'regular');
    });
    
    // Handle making a donor irregular
    $(document).on('click', '.make-irregular', function() {
        var donorId = $(this).data('id');
        updateDonorStatus(donorId, 'irregular');
    });
    
    function updateDonorStatus(donorId, status) {
        $.ajax({
            url: 'update_donor_status.php',
            type: 'POST',
            data: {
                donor_id: donorId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the status badge
                    var statusCell = $('button[data-id="' + donorId + '"]').closest('tr').find('.status-cell');
                    statusCell.html('<span class="status-badge status-' + status + '">' + 
                                   status.charAt(0).toUpperCase() + status.slice(1) + '</span>');
                    
                    // Update the button
                    var buttonCell = $('button[data-id="' + donorId + '"]').closest('td');
                    if (status === 'regular') {
                        buttonCell.html('<button class="btn btn-warning btn-sm make-irregular" data-id="' + donorId + '">Make Irregular</button>');
                    } else {
                        buttonCell.html('<button class="btn btn-success btn-sm make-regular" data-id="' + donorId + '">Make Regular</button>');
                    }
                    
                    alert('Donor status updated successfully!');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the donor status.');
            }
        });
    }

    // Blood type filtering
    $('.blood-filter').click(function() {
        var bloodType = $(this).data('blood');
        
        if (bloodType === 'all') {
            $('#donor-table tbody tr').show();
        } else {
            $('#donor-table tbody tr').hide();
            $('#donor-table tbody tr[data-blood="' + bloodType + '"]').show();
        }
        
        // Update active state of filter buttons
        $('.blood-filter').removeClass('active');
        $(this).addClass('active');
    });

    // Send notification to donors
    $("#sendNotification").click(function(){
        var bloodType = $("#notify-blood").val();
        if(!bloodType){
            alert("Please select a blood type first.");
            return;
        }
        var message = prompt("Enter notification message for all " + bloodType + " donors:");
        if(!message) return;

        $.post("send_donor_notification.php", {blood_type: bloodType, message: message}, function(data){
            var res = JSON.parse(data);
            alert(res.msg);
        });
    });

    // Request management
    $(".blood-type, .urgency").change(function(){
        var id = $(this).data("id");
        var field = $(this).hasClass('blood-type') ? 'blood_type' : 'urgency';
        var value = $(this).val();
        $.post("update_request.php", {id:id, field:field, value:value});
    });

    $(".accept-request").click(function(){
        var id = $(this).data("id");
        $.post("accept_request.php", {id:id}, function(){
            var countElem = $("#request-count");
            countElem.text(parseInt(countElem.text()) + 1);
            alert("Request accepted!");
        });
    });

    $(".remove-request").click(function(){
        if(confirm("Are you sure you want to remove this request?")){
            var id = $(this).data("id");
            $.post("remove_request.php", {id:id}, function(){ location.reload(); });
        }
    });

    // Search Blood Type in requests
    $("#search-blood-req").on("keyup", function(){
        var value = $(this).val().toLowerCase();
        var rows = $("#request-list tbody tr");
        rows.sort(function(a,b){
            var aText = $(a).find("td:nth-child(3) select").val().toLowerCase();
            var bText = $(b).find("td:nth-child(3) select").val().toLowerCase();
            return aText.localeCompare(bText);
        });
        $("#request-list tbody").html(rows);
        if(value){
            rows.each(function(){
                $(this).toggle($(this).find("td:nth-child(3) select").val().toLowerCase().indexOf(value) > -1);
            });
        } else {
            rows.show();
        }
    });
});
</script>
</body>
</html>
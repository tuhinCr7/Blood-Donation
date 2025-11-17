<?php
// Database configuration
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password
$dbname = "blood_management_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // For testing purposes - hardcoded credentials
    $valid_email = "admin@bloodmanagement.com";
    $valid_password = "admin123";
    
    // Check against hardcoded credentials first (for testing)
    if ($email === $valid_email && $password === $valid_password) {
        // Start session and redirect to admin profile
        session_start();
        $_SESSION['admin_id'] = 1;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_name'] = "System Administrator";
        
        header("Location: admin_profil.php");
        exit();
    }
    
    // If not using hardcoded credentials, check database
    $stmt = $conn->prepare("SELECT id, email, password, full_name FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verify password (assuming passwords are hashed with password_hash())
        if (password_verify($password, $admin['password'])) {
            // Start session and redirect to admin profile
            session_start();
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['full_name'];
            
            header("Location: admin_profil.php");
            exit();
        } else {
            // Invalid password
            header("Location: admin.html?error=invalid");
            exit();
        }
    } else {
        // No admin found with that email
        header("Location: admin.html?error=invalid");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>
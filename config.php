<?php
// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "event_management";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to redirect users based on role
function redirectUser($user_type) {
    if ($user_type == "admin") {
        header("Location: admin_dashboard.php");
    } elseif ($user_type == "staff") {
        header("Location: staff_dashboard.php");

    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}
?>

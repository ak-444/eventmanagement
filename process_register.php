<?php
session_start(); // Start session for user type check
include 'config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $user_type = $_POST['user_type'];
    $school_id = $_POST['school_id'];
    $department = $_POST['department'];

    // Default status for public registration
    $status = 'pending';

    // If the current user is an admin, set status to approved
    // In process_register.php, after retrieving $user_type
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
        // Restrict admins from creating other admins
        if ($user_type == 'admin') {
            die("Admins cannot create other admins.");
        }
    }
    // Prepare SQL statement
    $sql = "INSERT INTO users (username, email, password, user_type, school_id, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sssssss", $name, $email, $password, $user_type, $school_id, $department, $status);

        // Execute the statement
        if ($stmt->execute()) {
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
                header("Location: admin_user_management.php"); // Redirect admins to their dashboard
            } else {
                header("Location: login.php?success=1"); // Redirect public users to login
            }
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
 
    // Close connection
    $conn->close();
}

$_SESSION['error'] = "Registration failed: " . $stmt->error;
header("Location: register.php");
exit();

?>

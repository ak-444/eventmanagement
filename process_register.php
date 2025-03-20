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
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') {
        $status = 'approved';
    }

    // Prepare SQL statement
    $sql = "INSERT INTO users (username, email, password, user_type, school_id, department, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sssssss", $name, $email, $password, $user_type, $school_id, $department, $status);

        // Execute the statement
        if ($stmt->execute()) {
            header("Location: register.php?success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close connection
    $conn->close();
}
?>
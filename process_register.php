<?php
session_start();
include 'config.php';

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

    // Handle file upload
    $photoPath = null;
    if (isset($_FILES['id_photo']) && $_FILES['id_photo']['error'] === UPLOAD_ERR_OK) {
        // Create uploads directory if it doesn't exist
        $uploadDir = 'uploads/id_photos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileExt = pathinfo($_FILES['id_photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $fileExt;
        $targetPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($_FILES['id_photo']['tmp_name'], $targetPath)) {
            $photoPath = $targetPath;
        } else {
            header("Location: register.php?error=upload_failed");
            exit();
        }
    } else {
        header("Location: register.php?error=no_photo");
        exit();
    }

    // Prepare SQL statement
    $sql = "INSERT INTO users (username, email, password, user_type, school_id, department, status, id_photo_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("ssssssss", $name, $email, $password, $user_type, $school_id, $department, $status, $photoPath);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect directly to login.php instead of register.php with success parameter
            header("Location: login.php");
            exit();
        } else {
            header("Location: register.php?error=db_error");
            exit();
        }

        // Close statement
        $stmt->close();
    } else {
        header("Location: register.php?error=db_error");
        exit();
    }

    // Close connection
    $conn->close();
}
?>
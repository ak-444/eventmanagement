<?php
session_start();
include 'config.php'; // config

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    

    // Fetch user details
    $sql = "SELECT id, username, password, user_type FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashed_password, $user_type);
    
    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        // Store user details in session
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = $user_type;

         redirectuser($user_type);
    }
         else {
        echo "Invalid email or password.";
    }



    $stmt->close();
    $conn->close();
}
?>
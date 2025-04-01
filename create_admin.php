<?php
// create_admin.php

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "event_managements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin account details
$admin_data = [
    'username' => 'admin',
    'email' => 'admin1@example.com',
    'password' => 'admin123', // Change this to a strong password
    'school_id' => 'SCH001',
    'department' => 'Information Technology'
];

// Check if admin already exists
$check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ss", $admin_data['email'], $admin_data['username']);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Error: Admin account already exists!");
}

// Hash password
$hashed_password = password_hash($admin_data['password'], PASSWORD_BCRYPT);

// Insert admin into database
$insert_sql = "INSERT INTO users (
    username, 
    email, 
    password, 
    user_type, 
    school_id, 
    department, 
    status
) VALUES (?, ?, ?, 'admin', ?, ?, 'approved')";

$stmt = $conn->prepare($insert_sql);
$stmt->bind_param(
    "sssss",
    $admin_data['username'],
    $admin_data['email'],
    $hashed_password,
    $admin_data['school_id'],
    $admin_data['department']
);

if ($stmt->execute()) {
    echo "Admin account created successfully!<br>";
    echo "Username: " . $admin_data['username'] . "<br>";
    echo "Email: " . $admin_data['email'] . "<br>";
    echo "Password: " . $admin_data['password'] . "<br>";
    echo "Note: Please delete this file after use for security reasons!";
} else {
    echo "Error creating admin account: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
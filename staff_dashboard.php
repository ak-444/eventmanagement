<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}
echo "Welcome, Staff " . $_SESSION['username'];
?>

<a href="logout.php" class="btn btn-danger">Logout</a>
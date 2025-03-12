<?php
session_start();
require_once 'config.php'; // Include the database connection

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_message = "";

// Determine dashboard link based on user type
if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}

// Handle user form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $department = trim($_POST['department']);
    $school_id = trim($_POST['school_id']);
    
    // Handle image upload
    $image = $_FILES['profile_picture']['name'];
    $image_tmp = $_FILES['profile_picture']['tmp_name'];
    $image_folder = "uploads/" . basename($image);
    
    if (!empty($name) && !empty($email) && !empty($phone) && !empty($role) && !empty($department) && !empty($school_id) && !empty($image)) {
        move_uploaded_file($image_tmp, $image_folder);
        
        $stmt = $conn->prepare("INSERT INTO form_users (name, email, phone, role, department, school_id, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $role, $department, $school_id, $image_folder);

        if ($stmt->execute()) {
            echo "<script>alert('User added successfully!'); window.location.href='admin_user management.php';</script>";
        } else {
            echo "<script>alert('Error adding user: " . $conn->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all fields and upload a picture.');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <title>Event Management</title>

    <style>

        body {
            display: flex;
            background: #f4f4f4;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #293CB7, #1E2A78);
            padding-top: 20px;
            position: fixed;
            color: #ffffff;
            box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
        }
        .sidebar h4 {
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #f0f0f0;
            font-size: 16px;
            transition: background 0.3s ease, border-left 0.3s ease;
        }
        .sidebar a i {
            margin-right: 10px;
            font-size: 18px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 5px solid #fff;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-top: 10px;
        }
        .event-form {
            display: none;
            width: 350px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            top: 120px;
            left: 280px;
        }
    </style>

</head>


<body>

    <div class="sidebar">
        <h4>AU JAS</h4>
        <a href="<?php echo $dashboardLink; ?>"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_Event Calendar.php"><i class="bi bi-calendar"></i> Event Calendar</a>
        <a href="admin_Event Management.php" class="active"><i class="bi bi-gear"></i> Event Management</a>
        <a href="admin_user management.php"><i class="bi bi-people"></i> User Management</a>
        <a href="reports.php"><i class="bi bi-file-earmark-text"></i> Reports</a>
    </div>


    <div class="content">
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">User Management</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="event-header">
            <input type="text" class="form-control" placeholder="Search events..." style="width: 300px;">
            <div>
            <button class="btn btn-success">Users</button>
                <button class="btn btn-warning">Pending Users</button>
                <button class="btn btn-primary" onclick="location.href='admin_user form.php'">Add User</button>

            </div>
        </div>

        <div class="container mt-5">
    <a href="admin_user management.php" class="btn btn-secondary mb-3">Back to User Management</a>
    
    <div class="row">
        <!-- Add New User Form -->
        <div class="col-md-8">
            <div class="card shadow-sm p-4">
                <h2 class="mb-4">Add New User</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" name="role" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School ID</label>
                        <input type="text" class="form-control" name="school_id" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" name="profile_picture" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add User</button>
                </form>
            </div>
        </div>

        <!-- Access Levels Section -->
        <div class="col-md-4">
            <div class="card shadow-sm p-4">
                <h4 class="mb-3">Access Levels</h4>
               
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="editEvents" name="edit_events">
                    <label class="form-check-label" for="editEvents">Can edit events</label>
                </div>
            </div>
        </div>
    </div>
</div>



</body>
</html>
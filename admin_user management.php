<?php
session_start();
require_once 'config.php'; // Include the database connection
// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Determine dashboard link based on user type
if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}

if (isset($_GET['delete_id'])) {
    $userId = (int)$_GET['delete_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "User deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting user: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    // Redirect back to prevent refresh issues
    header("Location: admin_user management.php");
    exit();
}


// Get the current filename to determine the active page
$current_page = basename($_SERVER['PHP_SELF']);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <title></title>

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
            border-radius: 8px;
        }
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-top: 10px;
        }
        .search-bar-container {
            width: 300px;
            margin-right: 15px; /* Spacing between search and buttons */
        }

        .search-bar-container .form-control {
            border-radius: 20px;
            padding: 8px 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .search-bar-container .form-control:focus {
            border-color: #293CB7;
            box-shadow: 0 0 0 3px rgba(41, 60, 183, 0.1);
        }
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600; /* Add this line to make headers bold */
        }
        </style>
</head>

<body>

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="content">
    <nav class="navbar navbar-light">
        <div class="container-fluid d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">User Management</span>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item">User Type: <?= htmlspecialchars($_SESSION['user_type']); ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>

    <div class="event-header">
    <div class="search-bar-container">
        <input type="text" class="form-control" placeholder="Search users...">
    </div>
        <div>
            <button class="btn btn-success">Users</button>
            <button class="btn btn-warning" onclick="location.href='admin_pending_users.php'">Pending Users</button>
            <button class="btn btn-primary" onclick="location.href='admin_user form.php'">Add User</button>
        </div>
    </div>

    <!-- User Table -->
<section>
    <h2>User Details</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Department</th>
                <th>School ID</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $user_query = "SELECT id, username, email, user_type, department, school_id FROM users WHERE status != 'pending'";
            $user_result = $conn->query($user_query);

            if ($user_result->num_rows > 0) {
                $counter = 1;
                while ($row = $user_result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$counter}</td>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['email']) . "</td>
                            <td>" . htmlspecialchars($row['user_type']) . "</td>
                            <td>" . htmlspecialchars($row['department']) . "</td>
                            <td>" . htmlspecialchars($row['school_id']) . "</td>
                            <td>
                                <a href='admin_user_editpage.php?edit_id={$row['id']}' class='btn btn-warning btn-sm text-white'><i class='bi bi-pencil'></i> Edit</a>
                                <a href='?delete_id={$row['id']}' class='btn btn-danger btn-sm text-white' onclick='return confirm(\"Are you sure you want to delete this user?\");'><i class='bi bi-trash'></i> Delete</a>
                            </td>
                        </tr>";
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='7'>No users available</td></tr>";
            }
            ?>
        </tbody>
    </table>
</section>
</div>

</body>
</html>
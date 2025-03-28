<?php
session_start();
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get user ID from URL
if (!isset($_GET['edit_id'])) {
    header("Location: admin_user_management.php");
    exit();
}
$user_id = intval($_GET['edit_id']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $user_type = trim($_POST['user_type']);
    $department = trim($_POST['department']);
    $school_id = trim($_POST['school_id']);

    // Validate input
    if (empty($username) || empty($email) || empty($user_type)) {
        $_SESSION['error'] = "Please fill in all required fields";
    } else {
        // Update user in database
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, department = ?, school_id = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $username, $email, $user_type, $department, $school_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "User updated successfully";
        } else {
            $_SESSION['error'] = "Error updating user: " . $stmt->error;
        }
        $stmt->close();
    }
    // Redirect to avoid resubmission
    header("Location: admin_user_editpage.php?edit_id=" . $user_id);
    exit();
}

// Fetch user data after potential update
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

include 'sidebar.php';
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <style>
        body {
            display: flex;
            background: #f4f4f4;
        }

        .container {
            margin-top: 1.5rem; /* Additional spacing insurance */
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 1rem;
            max-width: 100%;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 1.5rem;
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
        .sidebar a:hover, 
        .sidebar a.active {
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

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Keep existing styles, add these modifications */
    

    
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

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
        
    <div class="container mt-4">
    <div class="row justify-content-center">
        <div class="form-container"> <!-- Add form-container div -->
            <div class="card shadow-sm p-4">
            <h2 class="mb-4"><i class="bi bi-pencil-square"></i> Edit User</h2>
                
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); endif; ?>

                <form method="POST">
                    <!-- Existing form fields remain the same -->
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" 
                            value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>User Type</label>
                        <select name="user_type" class="form-select" required>
                            <option value="admin" <?= $user['user_type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="staff" <?= $user['user_type'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                            <option value="user" <?= $user['user_type'] === 'user' ? 'selected' : '' ?>>User</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <input type="text" name="department" class="form-control"
                            value="<?= htmlspecialchars($user['department']) ?>">
                    </div>

                    <div class="form-group">
                        <label>School ID</label>
                        <input type="text" name="school_id" class="form-control"
                            value="<?= htmlspecialchars($user['school_id']) ?>">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="admin_user management.php" class="btn btn-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                             Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
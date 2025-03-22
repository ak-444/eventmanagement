<?php
session_start();
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$user = [];

// Get user ID from URL
if (isset($_GET['edit_id'])) {
    $user_id = intval($_GET['edit_id']);
    
    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        $error = "User not found";
    }
    $stmt->close();
} else {
    header("Location: admin_user_management.php");
    exit();
}

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
        $error = "Please fill in all required fields";
    } else {
        // Update user in database
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, department = ?, school_id = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $username, $email, $user_type, $department, $school_id, $user_id);
        
        if ($stmt->execute()) {
            $success = "User updated successfully";
            // Refresh user data
            $user = array_merge($user, [
                'username' => $username,
                'email' => $email,
                'user_type' => $user_type,
                'department' => $department,
                'school_id' => $school_id
            ]);
        } else {
            $error = "Error updating user: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="content">
    <nav class="navbar navbar-light">
        <!-- Same navbar as in user management page -->
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
    </nav>

    <div class="container">
        <h2 class="mb-4">Edit User</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
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
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="admin_user management.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $user_type = trim($_POST['user_type']);
    $department = trim($_POST['department']);
    $school_id = trim($_POST['school_id']);
    $password = trim($_POST['password']);
    
    // Set status based on admin session
    $status = (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') ? 'approved' : 'pending';
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Prepare SQL (include status)
    $stmt = $conn->prepare("INSERT INTO users (username, email, user_type, department, school_id, password, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $user_type, $department, $school_id, $password_hash, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully!";       
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
}
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

    <title>Event Management</title>

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
    </style>

</head>


<body>

   <!-- Sidebar -->
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
            <div class="form-container">
                <div class="card shadow-sm p-4">
                    <h2 class="mb-4"><i class="bi bi-person-plus"></i> Add New User</h2>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>User Type</label>
                            <select name="user_type" class="form-select" required>
                                <option value="user">User</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" class="form-select" required>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Accounting">Accounting</option>
                                <option value="Business">Business</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>School ID</label>
                            <input type="text" name="school_id" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="admin_user management.php" class="btn btn-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


</body>
</html>
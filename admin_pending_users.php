<?php
session_start();
require_once 'config.php';

// Redirect to login if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle approve/decline actions
if (isset($_GET['approve_id'])) {
    $userId = (int)$_GET['approve_id'];
    $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_message'] = "User approved successfully!";
    header("Location: admin_pending_users.php");
    exit();
}

if (isset($_GET['decline_id'])) {
    $userId = (int)$_GET['decline_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success_message'] = "User declined successfully!";
    header("Location: admin_pending_users.php");
    exit();
}

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
    <title>Pending User Approvals</title>
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --secondary-color: #f8fafc;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            display: flex;
            background: #f8fafc;
            margin: 0;
        }

         
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #293CB7, #1E2A78);
            padding-top: 20px;
            position: fixed;
            color: #ffffff;
            box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
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
            width: calc(100% - 270px);
            min-height: 100vh;
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-radius: 0;
        }

        .content-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            background: white;
            margin-top: 20px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 12px 15px;
            border-bottom: none;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .btn {
            border-radius: 6px;
            padding: 8px 14px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 6px 10px;
        }

        .search-container {
            position: relative;
            width: 100%;
            max-width: 350px;
        }

        .search-input {
            border-radius: 6px;
            padding: 8px 15px;
            padding-right: 40px;
            width: 100%;
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            z-index: 2;
            padding: 0;
            height: 20px;
            width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .btn-group-custom {
            display: flex;
            gap: 10px;
        }

        /* Modal image styling */
        #idPhotoModal img {
            max-width: 100%;
            max-height: 70vh;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }

        #studentIdNumber {
            font-size: 1.2rem;
            color: #293CB7;
            font-weight: bold;
        }

        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .btn-group-custom {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="content">
    <nav class="navbar navbar-light">
        <div class="container-fluid d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">User Management</span>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="content-card">
        <div class="page-header">
            <h2 class="page-title">Pending User Approvals</h2>
            <div class="btn-group-custom">
                <button class="btn btn-success" onclick="location.href='admin_user management.php'">Approved Users</button>
                <button class="btn btn-warning disabled">Pending Users</button>
            </div>
        </div>

        <div class="mb-4">
            <form method="get" action="" class="d-flex align-items-center">
                <div class="search-container">
                    <input type="text" name="search" class="form-control search-input" 
                           placeholder="Search pending users...">
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Requested Role</th>
                        <th>Department</th>
                        <th>School ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pending_query = "SELECT id, username, email, user_type, department, school_id, id_photo_path 
                                    FROM users WHERE status='pending' ORDER BY id DESC";
                    $result = $conn->query($pending_query);

                    if ($result->num_rows > 0) {
                        $counter = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$counter}</td>
                                    <td>".htmlspecialchars($row['username'])."</td>
                                    <td>".htmlspecialchars($row['email'])."</td>
                                    <td>".htmlspecialchars($row['user_type'])."</td>
                                    <td>".htmlspecialchars($row['department'])."</td>
                                    <td>".htmlspecialchars($row['school_id'])."</td>
                                    <td>
                                        <div class='action-buttons'>
                                            <button class='btn btn-info btn-sm me-2' 
                                                    onclick='viewIdPhoto({$row['id']}, \"{$row['school_id']}\", \"{$row['id_photo_path']}\")'>
                                                <i class='bi bi-eye'></i> View ID
                                            </button>
                                            <a href='?approve_id={$row['id']}' class='btn btn-success btn-sm me-2' 
                                               onclick='return confirm(\"Approve this user?\");'>
                                                <i class='bi bi-check-circle'></i> Approve
                                            </a>
                                            <a href='?decline_id={$row['id']}' class='btn btn-danger btn-sm' 
                                               onclick='return confirm(\"Decline and delete this request?\");'>
                                                <i class='bi bi-x-circle'></i> Decline
                                            </a>
                                        </div>
                                    </td>
                                  </tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-4'>
                                <i class='bi bi-inbox' style='font-size: 2rem; color: #94a3b8;'></i>
                                <p class='mt-2'>No pending user requests</p>
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for displaying ID photo -->
<div class="modal fade" id="idPhotoModal" tabindex="-1" aria-labelledby="idPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="idPhotoModalLabel">Student ID Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalIdPhoto" src="" alt="Student ID Photo" class="img-fluid">
                <p id="studentIdNumber" class="mt-3"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function viewIdPhoto(userId, schoolId, photoPath) {
        document.getElementById('modalIdPhoto').src = photoPath;
        document.getElementById('studentIdNumber').textContent = 'School ID: ' + schoolId;
        var modal = new bootstrap.Modal(document.getElementById('idPhotoModal'));
        modal.show();
    }
</script>

</body>
</html>
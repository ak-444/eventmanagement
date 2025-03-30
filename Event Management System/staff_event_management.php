<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userType = $_SESSION['user_type'];
$dashboardLink = ($userType == 'staff') ? 'staff_dashboard.php' : 'admin_dashboard.php';

$sql = "SELECT id, event_name, event_date, event_time, venue, status FROM events";
$result = $conn->query($sql);

if (!$result) die("Query failed: " . $conn->error);

include 'sidebar.php';
$current_page = basename($_SERVER['PHP_SELF']);
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
        /* Unified styling with admin version */
        body {
            display: flex;
            background: #f4f4f4;
       
        }

        .content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
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
            margin: 25px 0;
            gap: 15px;
        }

        .table thead th {
            background-color: #f8f9fa !important;
            font-weight: 600;
            vertical-align: middle;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }

        .btn-action {
            padding: 5px 10px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.2rem; /* Add this line */
        }

        @media (max-width: 768px) {
            .event-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    

<!-- Sidebar -->
<?php include 'sidebar.php'; ?>

<!-- Content -->
<div class="content">
    <!-- Updated Navbar -->
     <!-- Add this error display section -->
    <?php if (isset($_SESSION['error'])): ?>
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

<!-- Existing navbar code -->
    <nav class="navbar navbar-light">
        <div class="container-fluid d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">Event Submissions</span>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><div class="dropdown-item-text small text-muted">Role: <?= ucfirst($userType) ?></div></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    

    <!-- Added Event Header Section -->
    <div class="event-header">
        <input type="text" class="form-control" placeholder="Search submissions..." style="max-width: 300px;">
        <div class="button-group">
            <button class="btn btn-success">All Submissions</button>
            <button class="btn btn-primary" onclick="location.href='staff_event_form.php'">
                <i class="bi bi-plus-lg"></i> New Submission
            </button>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Authorization Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?= $_SESSION['error'] ?? '' ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


    <!-- Table Section -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['event_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($row['event_date'])) ?></td>
                            <td><?= htmlspecialchars($row['venue']) ?></td>
                            <td>
                                <span class="badge bg-<?= match($row['status']) {
                                    'Approved' => 'success',
                                    'Pending' => 'warning',
                                    'Rejected' => 'danger',
                                    default => 'secondary'
                                } ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="staff_event_view.php?id=<?= $row['id'] ?>" class="btn btn-info btn-action text-white">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($row['status'] == 'Pending'): ?>
                                 
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No submissions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    

</div>

</body>

</html>
<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $conn->begin_transaction();
    try {
        // 1. Delete from event_attendees
        $stmt1 = $conn->prepare("DELETE FROM event_attendees WHERE event_id = ?");
        $stmt1->bind_param("i", $delete_id);
        $stmt1->execute();
        $stmt1->close();
        
        // 2. Delete from event_staff
        $stmt2 = $conn->prepare("DELETE FROM event_staff WHERE event_id = ?");
        $stmt2->bind_param("i", $delete_id);
        $stmt2->execute();
        $stmt2->close();
        
        // 3. Delete from events
        $stmt3 = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt3->bind_param("i", $delete_id);
        $stmt3->execute();
        $stmt3->close();
        
        $conn->commit();
        
        $_SESSION['success_message'] = "Event deleted successfully!";
        header("Location: admin_Event Management.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting event: " . $conn->error;
        header("Location: admin_Event Management.php");
        exit();
    }
}

$sql = "SELECT id, event_name, event_date, event_time, venue FROM events WHERE status='Approved'";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
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

        /* Main Content */
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
            min-height: 100vh;
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

        /* Navbar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-radius: 0;
        }

        /* Modern Card Styling */
        .content-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }

        /* Header Styling */
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

        /* Table Styling */
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            background: white;
            margin-top: 20px;
        }
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
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
        .table tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }

        /* Button Styling */
        .btn {
            border-radius: 6px;
            padding: 8px 14px;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .btn-sm {
            padding: 6px 10px;
        }

        /* Search Bar */
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

        /* Alert Styling */
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        /* Button Group Styling */
        .btn-group-custom {
            display: flex;
            gap: 10px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .btn-group-custom {
                width: 100%;
                flex-wrap: wrap;
            }
            .btn-group-custom .btn {
                flex: 1 1 auto;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

    <div class="content">
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Event Management</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
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
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="content-card">
            <div class="page-header">
                <h2 class="page-title">Event Management</h2>
                <div class="btn-group-custom">
                    <button class="btn btn-success">Months</button>
                    <button class="btn btn-success">All Events</button>
                    <button class="btn btn-warning" onclick="location.href='admin_pending_events.php'">Pending Events</button>
                    <button class="btn btn-primary" onclick="location.href='admin_event form.php'">
                        <i class="bi bi-plus-lg"></i> Add Event
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <form method="get" action="" class="d-flex align-items-center">
                    <div class="search-container">
                        <input type="text" name="search" class="form-control search-input" 
                               placeholder="Search events...">
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
                            <th>Event Name</th>
                            <th>Event Date</th>
                            <th>Event Time</th>
                            <th>Venue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_time']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['venue']) . "</td>";
                                echo "<td>
                                        <div class='d-flex gap-2'>
                                            <a href='admin_view_events.php?id=" . $row['id'] . "' class='btn btn-info btn-sm text-white' title='View'>
                                                <i class='bi bi-eye'></i>
                                            </a>
                                            <a href='admin_edit_events.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm text-white' title='Edit'>
                                                <i class='bi bi-pencil'></i>
                                            </a>
                                            <a href='?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm text-white' title='Delete' onclick='return confirm(\"Are you sure you want to delete this event?\")'>
                                                <i class='bi bi-trash'></i>
                                            </a>
                                        </div>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4'>
                                    <i class='bi bi-inbox' style='font-size: 2rem; color: #94a3b8;'></i>
                                    <p class='mt-2'>No events available</p>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
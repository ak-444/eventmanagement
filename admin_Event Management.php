<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('Event deleted successfully!'); window.location.href='admin_Event Management.php';</script>";
    } else {
        echo "<script>alert('Error deleting event: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

$sql = "SELECT id, event_name, event_date, event_time, venue FROM events WHERE status='Approved'";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
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
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-top: 10px;
        }
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .table-bordered {
            border: 1px solid #dee2e6;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 12px;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        .form-control {
            border-radius: 4px;
            padding: 8px 12px;
        }
        .dropdown-menu {
            border: 1px solid rgba(0,0,0,.15);
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
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
            <div class="d-flex align-items-center">
                <input type="text" class="form-control search-bar" placeholder="Search events...">
            </div>
            <div class="button-group">
            <button class="btn btn-success">Months</button>
            <button class="btn btn-success">All Events</button>
            <button class="btn btn-warning" onclick="location.href='admin_pending_events.php'">Pending Events</button>
            <button class="btn btn-primary" onclick="location.href='admin_event form.php'">Add Event</button>
        </div>
        </div>

        <section>
            <h2>All Events</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Event Time</th>
                        <th>Venue</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row['id'] . "</td>
                                    <td>" . htmlspecialchars($row['event_name']) . "</td>
                                    <td>" . htmlspecialchars($row['event_date']) . "</td>
                                    <td>" . htmlspecialchars($row['event_time']) . "</td>
                                    <td>" . htmlspecialchars($row['venue']) . "</td>
                                    <td>
                                        <a href='?view_id=" . $row['id'] . "' class='btn btn-info btn-sm text-white'><i class='bi bi-eye'></i> View</a>
                                        <a href='?edit_id=" . $row['id'] . "' class='btn btn-warning btn-sm text-white'><i class='bi bi-pencil'></i> Edit</a>
                                        <a href='?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm text-white' onclick='return confirm(\"Are you sure you want to delete this event?\");'><i class='bi bi-trash'></i> Delete</a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No events available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['id'] ?? null;
if (!$event_id || !is_numeric($event_id)) {
    header("Location: admin_Event Management.php");
    exit();
}

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: admin_Event Management.php");
    exit();
}

// Fetch assigned staff
$assigned_staff = [];
try {
    $stmt = $conn->prepare("SELECT u.username, u.department 
                          FROM event_staff es
                          JOIN users u ON es.staff_id = u.id
                          WHERE es.event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assigned_staff = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log($e->getMessage());
}

// Fetch attendees with proper error handling
$attendees = [];
try {
    $stmt = $conn->prepare("SELECT u.username, u.school_id, u.department, ea.attended 
                       FROM event_attendees ea
                       JOIN users u ON ea.user_id = u.id
                       WHERE ea.event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendees = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    error_log("Attendee fetch error: " . $e->getMessage());
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Event View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Event Details</title>
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
            margin-bottom: 30px;
        }
        .event-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .attendee-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
         <!-- Navbar -->
         <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Event Details</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item">Role: Administrator</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <div class="container">
        <div class="event-header mb-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h2><?= htmlspecialchars($event['event_name']) ?></h2>
                </div>
                <a href="admin_edit_events.php?id=<?= $event_id ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Edit Event
                </a>
            </div>

            <!-- Attendance Summary -->
            <div class="mb-4">
                <p class="mb-2"><strong>Attendance Summary:</strong></p>
                <div class="d-flex gap-3">
                    <span class="badge bg-success">Present: <?= array_sum(array_column($attendees, 'attended')) ?></span>
                    <span class="badge bg-danger">Absent: <?= count($attendees) - array_sum(array_column($attendees, 'attended')) ?></span>
                </div>
            </div>

            <!-- Event Details -->
            <div class="row mt-2">
                <div class="col-md-4">
                    <p><strong>Date:</strong> <?= date('F j, Y', strtotime($event['event_date'])) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Time:</strong> <?= date('g:i A', strtotime($event['event_time'])) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
                </div>
            </div> <!-- Close the first row -->

            <!-- Add this new row for staff -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <p><strong>Assigned Staff:</strong></p>
                    <?php if (!empty($assigned_staff)): ?>
                        <ul class="list-group">
                            <?php foreach ($assigned_staff as $staff): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($staff['username']) ?> 
                                    <span class="text-muted">(<?= $staff['department'] ?>)</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-info">No staff assigned to this event</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-3">
                <p><strong>Description:</strong></p>
                <p><?= nl2br(htmlspecialchars($event['event_description'])) ?></p>
            </div>
        </div>

            <div class="attendee-table p-4">
                <h4>Attendees</h4>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendees as $attendee): ?>
                            <tr>
                                <td><?= htmlspecialchars($attendee['username']) ?></td>
                                <td><?= htmlspecialchars($attendee['school_id']) ?></td>
                                <td><?= htmlspecialchars($attendee['department']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $attendee['attended'] ? 'success' : 'danger' ?>">
                                        <?= $attendee['attended'] ? 'Present' : 'Absent' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
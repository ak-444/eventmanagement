<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['id'] ?? null;
$staff_id = $_SESSION['user_id'];

// Check authorization and get event details in one query
$stmt = $conn->prepare("SELECT e.* FROM events e 
                       INNER JOIN event_staff es ON e.id = es.event_id 
                       WHERE e.id = ? AND es.staff_id = ?");
$stmt->bind_param("ii", $event_id, $staff_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    $_SESSION['error'] = "You are not authorized to access this event.";
    header("Location: staff_event_management.php");
    exit();
}


// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Re-validate staff authorization
    $stmt = $conn->prepare("SELECT 1 FROM event_staff 
                           WHERE event_id = ? AND staff_id = ?");
    $stmt->bind_param("ii", $event_id, $staff_id);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        $_SESSION['error'] = "Authorization failed. Please try again.";
        header("Location: staff_dashboard.php");
        exit();
    }
    $stmt->close();

    // Process attendance update
    if (isset($_POST['attendance'])) {
        $conn->begin_transaction();
        try {
            $updateStmt = $conn->prepare("UPDATE event_attendees SET attended = ? WHERE event_id = ? AND user_id = ?");
            foreach ($_POST['attendance'] as $user_id => $status) {
                $attended = ($status === 'present') ? 1 : 0;
                $updateStmt->bind_param("iii", $attended, $event_id, $user_id);
                $updateStmt->execute();
            }
            $conn->commit();
            $_SESSION['success'] = "Attendance updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error updating attendance: " . $e->getMessage();
        }
        $updateStmt->close();
    } else {
        $_SESSION['error'] = "No attendance data submitted.";
    }
    
    header("Location: staff_event_view.php?id=" . $event_id);
    exit();
}

// Fetch attendees with attendance status
$attendees = [];
try {
    $stmt = $conn->prepare("SELECT u.id, u.username, u.school_id, u.department, ea.attended 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Event Details - Staff View</title>
    <!-- Include the same CSS/JS links as admin view -->
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
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Event Details</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><div class="dropdown-item-text small text-muted">Role: Staff</div></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['error']); endif; ?>
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
                <h2><?= htmlspecialchars($event['event_name']) ?></h2>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <p><strong>Date:</strong> <?= date('F j, Y', strtotime($event['event_date'])) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Time:</strong> <?= date('g:i A', strtotime($event['event_time'])) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></p>
                    </div>
                </div>
                <div class="mt-3">
                    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($event['event_description'])) ?></p>
                </div>
            </div>

            <form method="POST">
                <div class="attendee-table p-4">
                    <h4>Mark Attendance</h4>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Department</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendees as $attendee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($attendee['username']) ?></td>
                                    <td><?= htmlspecialchars($attendee['school_id']) ?></td>
                                    <td><?= htmlspecialchars($attendee['department']) ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="attendance[<?= $attendee['id'] ?>]" 
                                                   id="present_<?= $attendee['id'] ?>" 
                                                   value="present"
                                                   <?= $attendee['attended'] ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success" for="present_<?= $attendee['id'] ?>">Present</label>

                                            <input type="radio" 
                                                   class="btn-check" 
                                                   name="attendance[<?= $attendee['id'] ?>]" 
                                                   id="absent_<?= $attendee['id'] ?>" 
                                                   value="absent"
                                                   <?= !$attendee['attended'] ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger" for="absent_<?= $attendee['id'] ?>">Absent</label>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!empty($attendees)): ?>
                        <button type="submit" class="btn btn-primary mt-3">Save Attendance</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$event = [];
$attendees = [];

// Validate and get event ID
$event_id = $_GET['id'] ?? null;
if (!$event_id || !is_numeric($event_id)) {
    header("Location: admin_Event_management.php");
    exit();
}

// Fetch event details
try {
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    if (!$stmt) throw new Exception("Database error: " . $conn->error);
    
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) throw new Exception("Execution error: " . $stmt->error);
    
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();

    if (!$event) {
        header("Location: admin_Event_management.php");
        exit();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: admin_Event_management.php");
    exit();
}

// Fetch all users
$users = [];
try {
    $result = $conn->query("SELECT id, username, school_id, department FROM users WHERE status = 'approved'");
    if ($result) $users = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log($e->getMessage());
}

// Fetch current attendees
try {
    $stmt = $conn->prepare("SELECT u.id, u.username, u.school_id, u.department 
                          FROM event_attendees ea
                          JOIN users u ON ea.user_id = u.id
                          WHERE ea.event_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) $attendees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Update event details
        $event_name = $_POST['event_name'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $venue = $_POST['venue'];
        $description = $_POST['description'];

        // Validate required fields
        if (empty($event_name) || empty($event_date) || empty($event_time) || empty($venue)) {
            throw new Exception("All required fields must be filled");
        }

        // Update event
        $stmt = $conn->prepare("UPDATE events SET 
            event_name = ?,
            event_date = ?,
            event_time = ?,
            venue = ?,
            event_description = ?
            WHERE id = ?");

        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
        
        $stmt->bind_param("sssssi", 
            $event_name,
            $event_date,
            $event_time,
            $venue,
            $description,
            $event_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }
        $stmt->close();

        // Handle attendees
        $conn->begin_transaction();

        try {
            // Remove existing attendees
            $del_stmt = $conn->prepare("DELETE FROM event_attendees WHERE event_id = ?");
            if (!$del_stmt) throw new Exception("Delete prepare failed: " . $conn->error);
            $del_stmt->bind_param("i", $event_id);
            if (!$del_stmt->execute()) throw new Exception("Delete execute failed: " . $del_stmt->error);
            $del_stmt->close();

            // Insert new attendees if any
            if (!empty($_POST['attendees'])) {
                $insert_stmt = $conn->prepare("INSERT INTO event_attendees (event_id, user_id) VALUES (?, ?)");
                if (!$insert_stmt) throw new Exception("Insert prepare failed: " . $conn->error);

                foreach ($_POST['attendees'] as $user_id) {
                    if (!is_numeric($user_id)) continue;
                    $insert_stmt->bind_param("ii", $event_id, $user_id);
                    if (!$insert_stmt->execute()) {
                        throw new Exception("Insert execute failed: " . $insert_stmt->error);
                    }
                }
                $insert_stmt->close();
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

        // Redirect after successful update
        header("Location: admin_view_events.php?id=" . $event_id);
        exit();

    } catch (Exception $e) {
        error_log("Event update error: " . $e->getMessage());
        // You could add an error message to display to the user
        $error_message = "Error updating event: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            display: flex;
            background: #f4f4f4;
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
            width: calc(100% - 270px);
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .attendee-list {
            max-height: 300px; /* Adjust height */
            overflow-y: auto;
            margin-top: 15px;
        }

        .attendee-item {
            transition: background-color 0.2s;
        }
        .attendee-item:hover {
            background-color: #f8f9fa;
        }

        .form-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        height: 100%; /* Add full height */
        }   
        
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <nav class="navbar navbar-light mb-4">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Edit Event</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item">Role: Administrator</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <form method="POST">
                <div class="row g-4">
                    <!-- Left Column - Event Details -->
                    <div class="col-lg-8">
                        <div class="card p-4">
                            <h4 class="mb-4">Event Details</h4>
                            
                            <div class="mb-3">
                                <label class="form-label">Event Name</label>
                                <input type="text" class="form-control" name="event_name" 
                                    value="<?= htmlspecialchars($event['event_name'] ?? '') ?>" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Event Date</label>
                                    <input type="date" class="form-control" name="event_date" 
                                        value="<?= htmlspecialchars($event['event_date'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Event Time</label>
                                    <input type="time" class="form-control" name="event_time" 
                                        value="<?= htmlspecialchars($event['event_time'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Venue</label>
                                <input type="text" class="form-control" name="venue" 
                                    value="<?= htmlspecialchars($event['venue'] ?? '') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Event Description</label>
                                <textarea class="form-control" name="description" rows="4"><?= 
                                    htmlspecialchars($event['event_description'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                        </div>
                    </div>

                    <!-- Right Column - Attendees Management -->
                    <div class="col-lg-4">
                        <div class="card p-4">
                            <h4 class="mb-4">Manage Attendees</h4>

                            <div class="mb-3">
                                <label class="form-label">Add Users</label>
                                <div class="input-group">
                                    <select class="form-select" id="userSelect">
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['username']) ?> (<?= $user['school_id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary" onclick="addAttendee()">
                                        Add
                                    </button>
                                </div>
                            </div>

                            <div class="attendee-list mt-3">
                                <h5>Current Attendees</h5>
                                <div id="attendeesContainer" class="mt-2">
                                    <?php if (!empty($attendees)): ?>
                                        <?php foreach ($attendees as $attendee): ?>
                                            <div class="attendee-item d-flex justify-content-between align-items-center p-2 mb-2 rounded">
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($attendee['username']) ?></div>
                                                    <div class="text-muted small">
                                                        <?= $attendee['school_id'] ?> - <?= $attendee['department'] ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <input type="hidden" name="attendees[]" value="<?= $attendee['id'] ?>">
                                                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeAttendee(this)">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted">No attendees yet.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addAttendee() {
            const select = document.getElementById('userSelect');
            const user = select.options[select.selectedIndex];
            
            const container = document.getElementById('attendeesContainer');
            
            const div = document.createElement('div');
            div.className = 'attendee-item d-flex justify-content-between align-items-center p-2 mb-2 rounded';
            div.innerHTML = `
                <div>
                    <div class="fw-bold">${user.textContent.split(' (')[0]}</div>
                    <div class="text-muted small">${user.textContent.split(' (')[1].slice(0, -1)}</div>
                </div>
                <div>
                    <input type="hidden" name="attendees[]" value="${user.value}">
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeAttendee(this)">
                        Remove
                    </button>
                </div>
            `;
            
            container.appendChild(div);
        }

        function removeAttendee(button) {
            button.closest('.attendee-item').remove();
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
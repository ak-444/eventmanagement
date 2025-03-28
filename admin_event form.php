<?php
session_start();
require_once 'config.php';

// Redirect to login if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch approved users for attendee selection
$users = [];
$result = $conn->query("SELECT id, username, school_id, department FROM users WHERE status = 'approved'");
if ($result) $users = $result->fetch_all(MYSQLI_ASSOC);

$event_message = $attendee_message = "";

$stmt = $conn->prepare("SELECT id, username FROM users 
                       WHERE user_type = 'staff' AND status = 'approved'");
$stmt->execute();
$staff_members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        // Event details
        $event_name = trim($_POST['event_name']);
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $venue = trim($_POST['venue']);
        $event_description = trim($_POST['event_description']);

        // Validate inputs
        if (empty($event_name) || empty($event_date) || empty($event_time) || empty($venue)) {
            throw new Exception("All required fields must be filled");
        }

        // Insert event
        $stmt = $conn->prepare("INSERT INTO events 
            (event_name, event_date, event_time, venue, event_description, status) 
            VALUES (?, ?, ?, ?, ?, 'Approved')");
        $stmt->bind_param("sssss", $event_name, $event_date, $event_time, $venue, $event_description);
        
        if (!$stmt->execute()) throw new Exception("Event creation failed: " . $stmt->error);
        $event_id = $conn->insert_id;
        $stmt->close();

        if (!empty($_POST['staff'])) {
            $staff_ids = array_filter($_POST['staff'], 'is_numeric');
            
            $stmt = $conn->prepare("INSERT INTO event_staff (event_id, staff_id) VALUES (?, ?)");
            foreach ($staff_ids as $staff_id) {
                $stmt->bind_param("ii", $event_id, $staff_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // Handle attendees
        if (!empty($_POST['attendees'])) {
            // Filter and validate user IDs
            $attendees = array_filter($_POST['attendees'], 'is_numeric');
            if (!empty($attendees)) {
                // Create placeholders and parameters for bulk insert
                $placeholders = rtrim(str_repeat('(?, ?), ', count($attendees)), ', ');
                $types = str_repeat('ii', count($attendees));
                $params = [];
                foreach ($attendees as $user_id) {
                    $params[] = $event_id;
                    $params[] = $user_id;
                }
                
                // Prepare and execute bulk insert
                $stmt = $conn->prepare("INSERT INTO event_attendees (event_id, user_id) VALUES $placeholders");
                if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
                $stmt->bind_param($types, ...$params);
                if (!$stmt->execute()) throw new Exception("Attendee insertion failed: " . $stmt->error);
                $stmt->close();
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Event and attendees added successfully!"; // Changed from 'event_success'
        header("Location: admin_Event Management.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $event_message = "<div class='alert alert-danger'>{$e->getMessage()}</div>";
    }
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
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-top: 10px;
        }

        .event-form {
            display: none;
            width: 350px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            position: absolute;
            top: 100px;
            left: calc(50% - 175px); /* Centered form */
            z-index: 20;
        }

        .attendee-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 15px;
        }

        .attendee-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        .attendee-item:hover {
            background-color: #f8f9fa;
        }

        .form-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .attendee-box {
            height: calc(100% - 30px);
        }
        .form-select[multiple] {
            height: 150px;
            padding: 10px;
        }

        .form-select[multiple] option {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }

        .form-select[multiple] option:checked {
            background-color: #e3f2fd;
            color: #1a237e;
            font-weight: 500;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875em;
            margin-top: 0.5rem;
        }

        #noAttendeesMessage {
        display: none;
        color: #6c757d;
        }       

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <!-- ... (keep navbar unchanged) ... -->
        <nav class="navbar navbar-light mb-4">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Event Management</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item">Role: <?= htmlspecialchars($_SESSION['user_type']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <?php if (isset($_SESSION['form_errors'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['form_errors'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php unset($_SESSION['form_errors']); endif; ?>
                

        <div class="container mt-5">
    
            
            <form method="POST">
                <div class="row g-4">
                    <!-- Left Column - Event Details -->
                    <div class="col-lg-8">
                        <div class="form-section card-style">
                            <h4 class="mb-4"><i class="bi bi-calendar-event"></i> Event Details</h4>
                            <?= $event_message ?>
                            
                            <div class="row g-3">
                                <!-- Event input fields (keep unchanged) -->
                                <!-- ... existing form fields ... -->
                                <div class="col-md-6">
                                    <label class="form-label">Event Name</label>
                                    <input type="text" class="form-control" name="event_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Venue</label>
                                    <input type="text" class="form-control" name="venue" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Event Date</label>
                                    <input type="date" class="form-control" name="event_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Event Time</label>
                                    <input type="time" class="form-control" name="event_time" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="event_description" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Attendees -->
                    <div class="col-lg-4">
                        <div class="form-section card-style h-100 d-flex flex-column">
                            <h4 class="mb-4"><i class="bi bi-people-fill"></i> Manage Attendees</h4>
                            
                            <div class="flex-grow-1">
                                <div class="input-group mb-3">
                                    <select class="form-select" id="userSelect">
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= htmlspecialchars($user['username']) ?> (<?= $user['school_id'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-primary" onclick="addAttendee()">
                                        Add
                                    </button>
                                </div>
                                
                                <div class="attendee-list">
                                    <h5>Selected Attendees</h5>
                                    <div id="attendeesContainer" class="mt-2">
                                        <!-- Dynamic attendees -->
                                    </div>
                                    <div id="noAttendeesMessage" class="text-muted mt-2">No attendees selected. Please add some.
                                </div>
                            </div>

                            <div class="mt-4">
                                <h4 class="mb-4"><i class="bi bi-person-gear"></i> Assign Staff</h4>
                                    <div class="flex-grow-1">
                                        <select name="staff[]" multiple class="form-select" size="8">
                                            <?php foreach ($staff_members as $staff): ?>
                                                <option value="<?= $staff['id'] ?>">
                                                    <?= htmlspecialchars($staff['username']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text mt-2">Hold CTRL/CMD to select multiple staff members</div>
                                    </div>
                            </div>


                            <button type="submit" class="btn btn-success btn-lg w-100 mt-3">
                                <i class="bi bi-check-circle"></i> Create Event
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Keep JavaScript unchanged -->
    <script>
    function addAttendee() {
        const select = document.getElementById('userSelect');
        const user = select.options[select.selectedIndex];
        const container = document.getElementById('attendeesContainer');

        // Check if user already added
        const existing = Array.from(container.querySelectorAll('input'))
            .some(input => input.value === user.value);
        
        if (!existing) {
            const div = document.createElement('div');
            div.className = 'attendee-item d-flex justify-content-between align-items-center p-2 mb-2 rounded';
            div.innerHTML = `
                <div>
                    ${user.textContent.split(' (')[0]}
                    <input type="hidden" name="attendees[]" value="${user.value}">
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }
    }


    function checkAttendees() {
    const container = document.getElementById('attendeesContainer');
    const message = document.getElementById('noAttendeesMessage');
    message.style.display = container.children.length === 0 ? 'block' : 'none';
    }

    function removeAttendee(button) {
        button.closest('.attendee-item').remove();
        checkAttendees();
    }

    // Update addAttendee() function
    function addAttendee() {
        const select = document.getElementById('userSelect');
        const user = select.options[select.selectedIndex];
        const container = document.getElementById('attendeesContainer');

        const existing = Array.from(container.querySelectorAll('input'))
            .some(input => input.value === user.value);
        
        if (!existing) {
            const div = document.createElement('div');
            div.className = 'attendee-item d-flex justify-content-between align-items-center p-2 mb-2 rounded';
            div.innerHTML = `
                <div>
                    ${user.textContent.split(' (')[0]}
                    <input type="hidden" name="attendees[]" value="${user.value}">
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeAttendee(this)">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(div);
            checkAttendees();
        }
    }

    // Initial check when page loads
    document.addEventListener('DOMContentLoaded', checkAttendees);
    </script>
</body>
</html>
<?php
session_start();
require_once 'config.php'; // Include the database connection

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$event_message = $attendee_message = "";

// Determine dashboard link based on user type
if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}

// Handle event form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = trim($_POST['event_name']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $venue = trim($_POST['venue']);
    $event_description = trim($_POST['event_description']);

    // Validate form inputs
    if (!empty($event_name) && !empty($event_date) && !empty($event_time) && !empty($venue) && !empty($event_description)) {
        $stmt = $conn->prepare("INSERT INTO events (event_name, event_date, event_time, venue, event_description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $event_name, $event_date, $event_time, $venue, $event_description);

        if ($stmt->execute()) {
            echo "<script>alert('Event added successfully!'); window.location.href='admin_Event Management.php';</script>";
        } else {
            echo "<script>alert('Error adding event: " . $conn->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}



// Fetch events from the database
$sql = "SELECT * FROM events";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
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
            top: 120px;
            left: 280px;
        }
    </style>

</head>


<body>

    <div class="sidebar">
        <h4>AU JAS</h4>
        <a href="<?php echo $dashboardLink; ?>"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_Event Calendar.php"><i class="bi bi-calendar"></i> Event Calendar</a>
        <a href="admin_Event Management.php" class="active"><i class="bi bi-gear"></i> Event Management</a>
        <a href="admin_user management.php"><i class="bi bi-people"></i> User Management</a>
        <a href="reports.php"><i class="bi bi-file-earmark-text"></i> Reports</a>
    </div>


    <div class="content">
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Event Management</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
            <input type="text" class="form-control" placeholder="Search events..." style="width: 300px;">
            <div>
                <button class="btn btn-success">Months</button>
                <button class="btn btn-success">All Events</button>
                <button class="btn btn-warning">Pending Events</button>
                <button class="btn btn-primary" onclick="location.href='admin_event form.php'">Add Event</button>

            </div>
        </div>

        <div class="container mt-5">
        
        <a href="admin_Event Management.php" class="btn btn-secondary mb-3">Back to Event Management</a>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h4 class="mb-3">Add New Event</h4>
                    <?php echo $event_message; ?>

                    <form method="POST">
    <div class="mb-3">
        <label class="form-label">Event Name</label>
        <input type="text" class="form-control" name="event_name" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Event Date</label>
        <input type="date" class="form-control" name="event_date" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Event Time</label>
        <input type="time" class="form-control" name="event_time" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Venue</label>
        <input type="text" class="form-control" name="venue" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Event Description</label>
        <textarea class="form-control" name="event_description" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary w-100">Add Event</button>
</form>

                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h4 class="mb-3">Attendees Form</h4>
                    <?php echo $attendee_message; ?>
                </div>
                
                
    
        
    </div>
</body>
</html>
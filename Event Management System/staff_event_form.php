<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = $_POST['event_name'];
    $eventDate = $_POST['event_date'];
    $eventTime = $_POST['event_time'];
    $venue = $_POST['venue'];
    $description = $_POST['description'];

    // Remove submitted_by from the SQL query and parameters
    $stmt = $conn->prepare("INSERT INTO events 
        (event_name, event_date, event_time, venue, event_description, status) 
        VALUES (?, ?, ?, ?, ?, 'Pending')");
        
    // Change from "sssssi" to "sssss" (removed the integer parameter)
    $stmt->bind_param("sssss", $eventName, $eventDate, $eventTime, $venue, $description);

    if ($stmt->execute()) {
        $success = "Event submitted successfully!";
    } else {
        $error = "Error submitting event: " . $conn->error;
    }
    $stmt->close();
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
    <title>Create Event Submission</title>
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
            width: calc(100%);
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
        .form-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
    
<div class="content">
    <nav class="navbar navbar-light mb-4">
        <div class="container-fluid d-flex justify-content-between">
            <span class="navbar-brand mb-0 h1">Event Submission</span>
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

    <div class="container-fluid mt-5">  <!-- Changed to container-fluid -->
        <div class="form-container" style="margin-left: 1px; max-width: 1200px;">  <!-- Updated positioning -->
                <h4 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Event Submission Form</h4>
       
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3 mt-3">
                    <!-- Form fields remain the same -->
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
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="col-12 d-flex justify-content-between mt-4">
                        <a href="staff_event_management.php" class="btn btn-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send-check me-2"></i> Submit for Approval
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
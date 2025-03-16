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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Create Event Submission</title>
    <style>
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
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Create Event Submission</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Event Name</label>
                        <input type="text" class="form-control" name="event_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Event Date</label>
                        <input type="date" class="form-control" name="event_date" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Event Time</label>
                        <input type="time" class="form-control" name="event_time" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Venue</label>
                        <input type="text" class="form-control" name="venue" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Event Description</label>
                    <textarea class="form-control" name="description" rows="4" required></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="staff_event_management.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
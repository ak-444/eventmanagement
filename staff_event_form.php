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
    // Input sanitization
    $eventName = htmlspecialchars($_POST['event_name']);
    $eventDate = htmlspecialchars($_POST['event_date']);
    $eventTime = htmlspecialchars($_POST['event_time']);
    $venue = htmlspecialchars($_POST['venue']);
    $description = htmlspecialchars($_POST['description']);

    // File upload handling
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $uploadDir = 'uploads/documents/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (empty($_FILES['document']['name'])) {
        $error = "Please upload a document.";
    } else {
        $documentTmp = $_FILES['document']['tmp_name'];
        $documentName = basename($_FILES['document']['name']);
        $documentPath = $uploadDir . uniqid() . '_' . $documentName;
        $fileType = strtolower(pathinfo($documentPath, PATHINFO_EXTENSION));
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];

        // Validate file
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Only PDF, JPG, and PNG files are allowed.";
        } elseif ($_FILES['document']['size'] > $maxFileSize) {
            $error = "File size exceeds 5MB limit.";
        } elseif (!move_uploaded_file($documentTmp, $documentPath)) {
            $error = "Error uploading document.";
        } else {
            // Database insertion
            $stmt = $conn->prepare("INSERT INTO events 
                (event_name, event_date, event_time, venue, event_description, status, document_path) 
                VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
            
            if ($stmt && $stmt->bind_param("ssssss", $eventName, $eventDate, $eventTime, $venue, $description, $documentPath)) {
                if ($stmt->execute()) {
                    $success = "Event submitted successfully!";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
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
    max-width: 1200px; /* Changed from 700px */
    width: 100%; /* Added for better responsiveness */
    margin: 50px auto;
    padding: 40px; /* Increased padding */
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}
.form-control {
    width: 100%;
    padding: 12px 15px;
    font-size: 16px;
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

    <div class="container mt-5">
        <div class="form-container">
            <h4 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Event Submission Form</h4>
            
            <?php if ($error): ?>
                <div class="alert alert-danger mt-3"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success mt-3"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="mt-4">
                <div class="mb-3">
                    <label class="form-label">Event Name</label>
                    <input type="text" class="form-control" name="event_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Venue</label>
                    <input type="text" class="form-control" name="venue" required>
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
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Approval Request Document (PDF/Image)</label>
                    <input type="file" class="form-control" name="document" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted">Max file size: 5MB</small>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="staff_event_management.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-check me-2"></i> Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

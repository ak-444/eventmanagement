<?php
session_start();
require_once 'config.php'; // Include the database connection

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Determine dashboard link based on user type
if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}



// Handle delete event request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prepare SQL statement to delete the event
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('Event deleted successfully!'); window.location.href='admin_Event Management.php';</script>";
    } else {
        echo "<script>alert('Error deleting event: " . $conn->error . "');</script>";
    }

    $stmt->close();
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

    
        <section>
            <h2>All Events</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row['id'] . "</td>
                                    <td>" . $row['event_name'] . "</td>
                                    <td>" . $row['event_date'] . "</td>
                                    <td>
                                        <a href='?view_id=" . $row['id'] . "' class='btn btn-info btn-sm text-white'><i class='bi bi-eye'></i> View</a> 
                                        <a href='?edit_id=" . $row['id'] . "' class='btn btn-warning btn-sm text-white'><i class='bi bi-pencil'></i> Edit</a> 
                                        <a href='?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm text-white' onclick='return confirm(\"Are you sure you want to delete this event?\");'><i class='bi bi-trash'></i> Delete</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No events available</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>




        <script>
            document.getElementById('showEventForm').addEventListener('click', function() {
                document.getElementById('eventForm').style.display = 'block';

            
            });
        </script>
    </div>
</body>
</html>
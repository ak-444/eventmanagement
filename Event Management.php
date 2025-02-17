<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "event_management");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle event submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event']) && $_POST['csrf_token'] == $_SESSION['csrf_token']) {
    $event_name = $conn->real_escape_string(trim($_POST['event_name']));
    $event_date = $conn->real_escape_string(trim($_POST['event_date']));
    $event_description = $conn->real_escape_string(trim($_POST['event_description']));

    // Insert the event into the database
    $sql = "INSERT INTO events (event_name, event_date, event_description) 
            VALUES ('$event_name', '$event_date', '$event_description')";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['event_message'] = "New event created successfully!"; // Store the success message in the session
            // Remove the following redirection lines to stop automatic redirection
            // if ($_SESSION['user_type'] == 'admin') {
            //     header("Location: admin_dashboard.php"); // Redirect to admin page
            // } elseif ($_SESSION['user_type'] == 'staff') {
            //     header("Location: staff_dashboard.php"); // Redirect to user page
            // } else {
            //     header("Location: user_page.php"); // Redirect to default page if user type is unknown
            // }
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
}

// Handle event deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prepare and delete the event from the database
    $sql = "DELETE FROM events WHERE id = $delete_id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Event deleted successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';  // Admin dashboard link
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';  // Staff dashboard link
} else {
    $dashboardLink = 'user_dashboard.php';  // User dashboard link
}

// Fetch existing events
$sql = "SELECT * FROM events";
$result = $conn->query($sql);
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
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: #f0f0f0;
            transition: background 0.3s ease;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
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
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center">AU JAS</h4>
        <a href="<?php echo $dashboardLink; ?>"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="Event Calendar.php"><i class="bi bi-calendar"></i> Event Calendar</a>
        <a href="Event Management.php"><i class="bi bi-gear"></i> Event Management</a>
        <a href="user_management.php"><i class="bi bi-people"></i> User Management</a>
        <a href="reports.php"><i class="bi bi-file-earmark-text"></i> Reports</a>
    </div>

    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1" id="headerTitle">Dashboard</span>
                
                <!-- User Info -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php
                        if (isset($_SESSION['username'])) {
                            echo htmlspecialchars($_SESSION['username']);
                        } else {
                            echo "User not logged in";
                        }
                        ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Add Event Form -->
        <section class="mb-5">
            <h2>Add New Event</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="mb-3">
                    <label for="event_name" class="form-label">Event Name</label>
                    <input type="text" class="form-control" id="event_name" name="event_name" required>
                </div>
                <div class="mb-3">
                    <label for="event_date" class="form-label">Event Date</label>
                    <input type="date" class="form-control" id="event_date" name="event_date" required>
                </div>
                <div class="mb-3">
                    <label for="event_description" class="form-label">Event Description</label>
                    <textarea class="form-control" id="event_description" name="event_description" required></textarea>
                </div>
                <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
            </form>
        </section>

        <!-- Success Message -->
        <?php
        if (isset($_SESSION['event_message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['event_message'] . "</div>";
            unset($_SESSION['event_message']);
        }
        ?>

        <!-- Event List Section -->
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
                                        <a href='?view_id=" . $row['id'] . "' class='btn btn-info btn-sm text-white'>
                                            <i class='fas fa-eye'></i> View
                                        </a> 
                                        <a href='?edit_id=" . $row['id'] . "' class='btn btn-warning btn-sm text-white'>
                                            <i class='fas fa-edit'></i> Edit
                                        </a> 
                                        <a href='?delete_id=" . $row['id'] . "' class='btn btn-danger btn-sm text-white' onclick='return confirm(\"Are you sure you want to delete this event?\");'>
                                            <i class='fas fa-trash-alt'></i> Delete
                                        </a>
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
    </div>
</body>
</html>

<?php
$conn->close();
?>

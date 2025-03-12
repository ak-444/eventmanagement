<?php
session_start();
require_once 'config.php'; // Include the database connection

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
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
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <title>Event Calendar</title>
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
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px; /* Increased margin for better spacing */
            padding-top: 10px; /* Added padding for spacing */
        }
        .search-bar {
            width: 300px;
        }
        .month-box {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        .month-btn {
            width: 100px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .month-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4>AU JAS</h4>
        <a href="<?php echo $dashboardLink; ?>"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_Event Calendar.php" class="active"><i class="bi bi-calendar"></i> Event Calendar</a>
        <a href="admin_Event Management.php"><i class="bi bi-gear"></i> Event Management</a>
        <a href="admin_user management.php"><i class="bi bi-people"></i> User Management</a>
        <a href="reports.php"><i class="bi bi-file-earmark-text"></i> Reports</a>
    </div>

    <div class="content">
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1" id="headerTitle">Event Calendar</span>
                
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

        <div class="calendar-header">
            <input type="text" class="form-control search-bar" placeholder="Search events...">
            <div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#monthModal">Months View</button>
                <button class="btn btn-success">All Events</button>
                <button class="btn btn-primary">Add Event</button>
            </div>
        </div>
        
        <div id='calendar'></div>
    </div>

    <!-- Month Selection Modal -->
    <div class="modal fade" id="monthModal" tabindex="-1" aria-labelledby="monthModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthModalLabel">Select a Month</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="month-box">
                        <?php
                        $months = [
                            "January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December"
                        ];
                        foreach ($months as $index => $month) {
                            echo "<button class='month-btn' onclick='changeMonth($index)'>$month</button>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth'
            });
            calendar.render();

            window.changeMonth = function(monthIndex) {
                var year = new Date().getFullYear();
                calendar.gotoDate(new Date(year, monthIndex, 1));
                var modal = bootstrap.Modal.getInstance(document.getElementById('monthModal'));
                modal.hide();
            }
        });
    </script>
</body>
</html>

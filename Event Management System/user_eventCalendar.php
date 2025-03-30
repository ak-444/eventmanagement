<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure the user type is 'user' to show the user event calendar
if ($_SESSION['user_type'] !== 'user') {
    header("Location: " . $dashboardLink); // Redirect to the appropriate dashboard
    exit();
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
    <title>User Event Calendar</title>
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
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar {
            width: 300px;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <h4>AU JAS</h4>
        <a href="user_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i> Dashboard
        </a>
        <a href="user_eventCalendar.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_eventCalendar.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar"></i> Event Calendar
        </a>
        <a href="user_evaluation.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_evaluation.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard"></i> Evaluation
        </a>
    </div>

    <div class="content">
         <!-- Navbar -->
         <nav class="navbar navbar-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1" id="headerTitle">User Event Calendar</span>
                
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

        <div class="calendar-header">
            <input type="text" class="form-control search-bar" placeholder="Search events...">
        </div>
        
        <div id='calendar'></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    {
                        title: 'User Event Example',
                        start: '2025-02-18'
                    }
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>

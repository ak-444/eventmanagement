<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Dashboard redirection logic
if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}

// Fetch events
$sql = "SELECT * FROM events WHERE status = 'Approved'"; // Show only approved events
$result = $conn->query($sql);
$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['event_name'],
        'start' => $row['event_date'], // Remove time to make it all-day
        'description' => $row['event_description'],
        'venue' => $row['venue'],
        'status' => $row['status'],
        'color' => '#ffc107' // Force yellow color for all approved events
    ];
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    
    <!-- Styles & Scripts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    
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
        .sidebar a:hover, 
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 5px solid #fff;
        }
        
        .content {
            margin-left: 260px;
            padding: 20px;
            flex-grow: 1;
        }

        .navbar {
            background-color: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dropdown-toggle::after {
            margin-left: 8px;
        }

        .btn-light {
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .btn-light:hover {
            background-color: #f8f9fa;
        }
        
        .calendar-tools {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .fc-toolbar-title {
            color: #1E2A78;
            font-weight: 600;
        }

        .fc-day:hover {
            background-color: rgba(41, 60, 183, 0.05) !important;
        }

        .fc-day-highlight {
            background-color: rgba(41, 60, 183, 0.1) !important;
            transition: background-color 0.3s ease;
        }

        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        #monthYearText {
            font-size: 1.75rem;
            letter-spacing: 0.5px;
            color: #1E2A78;
        }

        .search-bar-container {
            width: 300px;
            margin-right: 15px; /* Spacing between search and buttons */
        }

        .search-bar-container .form-control {
            border-radius: 20px;
            padding: 8px 15px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .search-bar-container .form-control:focus {
            border-color: #293CB7;
            box-shadow: 0 0 0 3px rgba(41, 60, 183, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Event Calendar</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Calendar Tools -->
        <div class="calendar-tools">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2">
                <div class="search-bar-container">
                    <input type="text" class="form-control" placeholder="Search events...">
                </div>
                    <?php if($_SESSION['user_type'] == 'admin'): ?>
                    <button class="btn btn-primary" onclick="location.href='admin_event form.php'">
                        <i class="bi bi-plus-lg"></i> Add Event
                    </button>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary">Today</button>
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary active">Month</button>
                        <button class="btn btn-outline-secondary">Week</button>
                        <button class="btn btn-outline-secondary">Day</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="calendar-header mb-3">
            <h3 id="monthYearText" class="text-primary fw-bold"></h3>
        </div>

        

        <!-- Calendar Container -->
        <div id="calendar"></div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const calendarEl = document.getElementById('calendar');
        const monthYearText = document.getElementById('monthYearText');
        
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: '',
                center: '',
                right: ''
            },
            events: <?= json_encode($events) ?>,
            datesSet: function(info) {
                // Update month/year display
                const start = info.view.currentStart;
                const month = start.toLocaleString('default', { month: 'long' });
                const year = start.getFullYear();
                monthYearText.textContent = `${month} ${year}`;
            },
            eventDidMount: (info) => {
                const event = info.event;
                const description = event.extendedProps.description || 'No description';
                const venue = event.extendedProps.venue || 'No venue specified';
                
                info.el.setAttribute('title', 
                    `${event.title}\n
                    Description: ${description}\n
                    Venue: ${venue}`);
                
                // Permanently highlight the day cell
                const dayCell = document.querySelector(`.fc-day[data-date="${event.startStr}"]`);
                if (dayCell) {
                    dayCell.style.backgroundColor = 'rgba(255, 193, 7, 0.2)'; // Yellow highlight
                }
                
                // Remove hover effects if needed
                info.el.style.cursor = 'default';
                info.el.style.boxShadow = 'none';
            }
        });
        
        calendar.render();
        
        // Initial month/year display
        const currentDate = calendar.getDate();
        const month = currentDate.toLocaleString('default', { month: 'long' });
        const year = currentDate.getFullYear();
        monthYearText.textContent = `${month} ${year}`;
        
        // View change buttons
        document.querySelector('.btn-group').addEventListener('click', (e) => {
            if (e.target.tagName === 'BUTTON') {
                const viewType = e.target.textContent.toLowerCase();
                calendar.changeView(`dayGrid${viewType.charAt(0).toUpperCase() + viewType.slice(1)}`);
                
                // Update active state on buttons
                document.querySelectorAll('.btn-group .btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                e.target.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>
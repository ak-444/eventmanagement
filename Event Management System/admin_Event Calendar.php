<?php
session_start();
require_once 'config.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'sidebar.php';

if ($_SESSION['user_type'] == 'admin') {
    $dashboardLink = 'admin_dashboard.php';
} elseif ($_SESSION['user_type'] == 'staff') {
    $dashboardLink = 'staff_dashboard.php';
} else {
    $dashboardLink = 'user_dashboard.php';
}

// Fetch events from database
$sql = "SELECT * FROM events WHERE status = 'Approved'";  // Only show approved events
$result = $conn->query($sql);
$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['event_name'],
        'start' => $row['event_date'] . 'T' . $row['event_time'],
        'description' => $row['description'],
        'venue' => $row['venue']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Keep existing head section -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?= json_encode($events) ?>,
                eventDidMount: function(info) {
                    // Add custom tooltip
                    info.el.setAttribute('title', `${info.event.title}\n${info.event.extendedProps.description}\nVenue: ${info.event.extendedProps.venue}`);
                }
            });
            calendar.render();

            window.changeMonth = function(monthIndex) {
                calendar.gotoDate(new Date(calendar.getDate().getFullYear(), monthIndex, 1));
                $('#monthModal').modal('hide');
            }
        });
    </script>
</head>
<body>
    <!-- Dynamic Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <!-- Keep existing content section -->
        <div class="calendar-header">
            <input type="text" class="form-control search-bar" placeholder="Search events...">
            <div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#monthModal">Months View</button>
                <button class="btn btn-success">All Events</button>
                <?php if($_SESSION['user_type'] == 'admin'): ?>
                    <button class="btn btn-primary" onclick="location.href='admin_event_form.php'">Add Event</button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Rest of the calendar content -->
    </div>
</body>
</html>
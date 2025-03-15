<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'sidebar.php';

// Fetch events based on user type
$sql = "SELECT * FROM events";
if ($_SESSION['user_type'] != 'admin') {
    $sql .= " WHERE status = 'Approved'";
}

$result = $conn->query($sql);
$events = [];
while($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['event_name'],
        'start' => $row['event_date'] . 'T' . $row['event_time'],
        'description' => $row['description'],
        'venue' => $row['venue'],
        'color' => $row['status'] == 'Pending' ? '#ffc107' : ($row['status'] == 'Rejected' ? '#dc3545' : '#28a745')
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Keep existing head content -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: <?= json_encode($events) ?>,
                eventDidMount: function(info) {
                    info.el.setAttribute('title', 
                        `${info.event.title}\n
                        Description: ${info.event.extendedProps.description}\n
                        Venue: ${info.event.extendedProps.venue}\n
                        Status: ${info.event.extendedProps.status}`);
                }
            });
            calendar.render();
        });
    </script>
</head>
<body>
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
    </div>
</body>
</html>
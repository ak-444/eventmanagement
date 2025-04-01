<?php
$conn = new mysqli("localhost", "root", "", "event");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get all events
$events = $conn->query("SELECT * FROM events");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Attendees</title>
</head>
<body>
    <h1>Events</h1>
    <?php while ($event = $events->fetch_assoc()): ?>
        <h2><?= htmlspecialchars($event['name']) ?> (<?= htmlspecialchars($event['date']) ?>)</h2>
        <img src="<?= htmlspecialchars($event['qr_code_path']) ?>" alt="QR Code" width="200"><br>
        <h3>Attendees:</h3>
        <ul>
            <?php
            // Query to get attendees for the specific event using the correct column 'event_id'
            $attendees = $conn->query("SELECT * FROM attendees WHERE event_id = " . $event['event_id']);
            while ($attendee = $attendees->fetch_assoc()):
            ?>
                <li><?= htmlspecialchars($attendee['name']) ?> (<?= htmlspecialchars($attendee['email']) ?>)</li>
            <?php endwhile; ?>
        </ul>
    <?php endwhile; ?>
</body>
</html>

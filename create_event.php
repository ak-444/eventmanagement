<?php
include 'phpqrcode/qrlib.php'; // Include PHP QR Code Library

// Database connection
$conn = new mysqli("localhost", "root", "", "event");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eventName = $_POST['event_name'];
    $eventDate = $_POST['event_date'];

    // Insert event into database
    $stmt = $conn->prepare("INSERT INTO events (name, date) VALUES (?, ?)");
    $stmt->bind_param("ss", $eventName, $eventDate);
    $stmt->execute();
    $eventId = $stmt->insert_id; // Get the last inserted event_id

    // Generate unique QR Code
    $qrData = "http://localhost/event/attendance.php?event_id=$eventId";
    $qrFileName = "qrcodes/event_$eventId.png";
    QRcode::png($qrData, $qrFileName, QR_ECLEVEL_L, 10);

    // Save QR code path to database (use event_id instead of id)
    $stmt = $conn->prepare("UPDATE events SET qr_code_path = ? WHERE event_id = ?");
    $stmt->bind_param("si", $qrFileName, $eventId);
    $stmt->execute();

    echo "Event created successfully! <a href='view_attendees.php'>View Events</a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Event</title>
</head>
<body>
    <h1>Create Event</h1>
    <form method="POST">
        <label>Event Name:</label><br>
        <input type="text" name="event_name" required><br>
        <label>Event Date:</label><br>
        <input type="date" name="event_date" required><br>
        <button type="submit">Create Event</button>
    </form>
</body>
</html>
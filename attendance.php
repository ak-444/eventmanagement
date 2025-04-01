<?php
$conn = new mysqli("localhost", "root", "", "event");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get data from the form
    $eventId = $_POST['event_id'];
    $name = $_POST['name'];
    $schoolId = $_POST['school_id'];
    $email = $_POST['email'];

    // Insert the attendee's data into the database
    $stmt = $conn->prepare("INSERT INTO attendees (event_id, name, school_id, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $eventId, $name, $schoolId, $email);
    if ($stmt->execute()) {
        echo "Thank you for registering! <br><a href='view_attendees.php'>View Attendees</a>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Event Attendance</title>
</head>
<body>
    <h1>Event Attendance</h1>
    <form method="POST">
        <input type="hidden" name="event_id" value="<?= $_GET['event_id'] ?>">= <!-- Hidden input to store event ID -->
        
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" required><br>
        
        <label for="school_id">School ID:</label><br>
        <input type="text" id="school_id" name="school_id" required><br>
        
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <button type="submit">Submit</button>
    </form>
</body>
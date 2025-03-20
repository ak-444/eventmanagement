<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['user', 'staff', 'admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $responses = $_POST['responses'] ?? [];

    // Save responses to the database
    foreach ($responses as $index => $response) {
        $question_index = $index + 1;
        $stmt = $conn->prepare("INSERT INTO evaluations (event_id, user_id, question_index, response) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $event_id, $_SESSION['user_id'], $question_index, $response);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>alert('Evaluation submitted successfully!'); window.location.href='user_evaluation.php';</script>";
}
?>
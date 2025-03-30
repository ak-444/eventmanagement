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
    // In submit_evaluation.php
foreach ($_POST['responses'] as $question_id => $response_text) {
    // Insert each response into the database
    $stmt = $conn->prepare("INSERT INTO answers (question_id, user_id, answer_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $question_id, $_SESSION['user_id'], $response_text);
    $stmt->execute();
    $stmt->close();
}

    echo "<script>alert('Evaluation submitted successfully!'); window.location.href='user_evaluation.php';</script>";
}
?>
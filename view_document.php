<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_pending_events.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);
$stmt = $conn->prepare("SELECT document_path FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

if (!$document || !file_exists($document['document_path'])) {
    header("HTTP/1.1 404 Not Found");
    exit("Document not found");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $document['document_path']);
finfo_close($finfo);

header("Content-Type: " . $mime);
header("Content-Length: " . filesize($document['document_path']));
readfile($document['document_path']);
exit();
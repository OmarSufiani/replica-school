<?php
session_start();
include 'db.php';

// ✅ Access control
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher','dean'])) {
    die("Access denied.");
}

if (!isset($_GET['id'])) {
    die("❌ Invalid request. Exam ID missing.");
}

$examId = intval($_GET['id']);
$schoolId = $_SESSION['school_id'];

// Fetch file info for this exam & school
$stmt = $conn->prepare("SELECT exam_name, file_path FROM exam WHERE id=? AND school_id=?");
$stmt->bind_param("ii", $examId, $schoolId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Exam not found or you don’t have permission.");
}

$row = $result->fetch_assoc();
$filePath = $row['file_path'];
$fileName = basename($filePath);

// Check file exists on server
if (!file_exists($filePath)) {
    die("❌ File not found on server: " . htmlspecialchars($filePath));
}

// ✅ Send file to browser
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;

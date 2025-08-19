<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location:  ../login.php');
    exit();
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('No file specified.');
}

$uploadDir = 'uploads/';
$file = $_GET['file'];

// Normalize and build absolute path to file
$filePath = realpath($uploadDir . $file);

// Check file exists
if (!$filePath || !file_exists($filePath)) {
    die('File does not exist.');
}

// Security check: Ensure the file is inside uploads folder (prevent path traversal)
if (strpos($filePath, realpath($uploadDir)) !== 0) {
    die('Invalid file path.');
}

// Delete the file
if (unlink($filePath)) {
    // Redirect back with success message (adjust the location if needed)
    header('Location: file.php?msg=File deleted successfully');
    exit();
} else {
    die('Failed to delete the file.');
}
?>

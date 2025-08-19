<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Allowed file types
$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'text/plain',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

$maxFileSize = 5 * 1024 * 1024; // 5MB
$uploadDir = 'uploads/exams/';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            $message = "❌ File type not allowed. Only PDF, DOC, DOCX, XLS, XLSX or TXT are accepted.";
        } elseif ($fileSize > $maxFileSize) {
            $message = "❌ File too large. Maximum size allowed is 5MB.";
        } else {
            $fileNameSafe = preg_replace("/[^A-Za-z0-9_\-\.]/", '_', pathinfo($fileName, PATHINFO_FILENAME));
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = time() . '-' . $fileNameSafe . '.' . $fileExtension;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $message = "✅ File uploaded successfully as <strong>$newFileName</strong>";
            } else {
                $message = "❌ Error moving the uploaded file.";
            }
        }
    } else {
        $message = "❌ File upload error. Code: " . $_FILES['file']['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload File</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow">
        <div class="card-header text-center bg-primary text-white">
          <h4>Upload Exam</h4>
        </div>
        <div class="card-body">

          <?php if (!empty($message)): ?>
            <div id="messageBox" class="alert <?= strpos($message, '✅') === 0 ? 'alert-success' : 'alert-danger' ?>">
              <?= $message ?>
            </div>
            <script>
              setTimeout(() => {
                const msgBox = document.getElementById('messageBox');
                if (msgBox) {
                  msgBox.style.display = 'none';
                }
              }, 4000);
            </script>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="file" class="form-label">Choose File</label>
              <input type="file" name="file" id="file" class="form-control" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">Upload</button>
            </div>
          </form>

          <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm me-2">&larr; Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>

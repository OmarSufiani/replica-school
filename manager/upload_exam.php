<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php'; // adjust path if needed

// Allow only teachers to upload
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    die("❌ Access denied. Only teachers can upload exams.");
}

$userId   = $_SESSION['user_id'];   // from users table
$schoolId = $_SESSION['school_id']; // logged-in school

$error = '';
$success = '';

// ✅ Get teacher ID for logged-in user
$teacherId = null;
$stmtTeacher = $conn->prepare("SELECT id FROM teacher WHERE user_id = ? AND school_id = ?");
$stmtTeacher->bind_param("ii", $userId, $schoolId);
$stmtTeacher->execute();
$resTeacher = $stmtTeacher->get_result();

if ($resTeacher->num_rows > 0) {
    $rowTeacher = $resTeacher->fetch_assoc();
    $teacherId = $rowTeacher['id'];
} else {
    die("❌ You are not registered as a teacher in this school.");
}
$stmtTeacher->close();

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examName  = $_POST['exam_name'];
    $term      = $_POST['term'];
    $examType  = $_POST['exam_type'];
    $year      = $_POST['year'];
    $subject   = $_POST['subject'];

    $uploadDir = "../uploads/exams/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['exam_file']['name']);
    $targetFilePath = $uploadDir . time() . "_" . $fileName;

    if (move_uploaded_file($_FILES['exam_file']['tmp_name'], $targetFilePath)) {
        // ✅ Insert into exam table
        $stmt = $conn->prepare("INSERT INTO exam 
            (exam_name, term, exam_type, year, subject, file_path, school_id, teacher_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssii",
            $examName,
            $term,
            $examType,
            $year,
            $subject,
            $targetFilePath,
            $schoolId,
            $teacherId
        );

        if ($stmt->execute()) {
            $success = "✅ Exam uploaded successfully.";
        } else {
            $error = "❌ Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "❌ Failed to upload file.";
    }
}
?>

<!DOCTYPE html>

<head>
     <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Exam</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="container mt-5">
 
    <h3>Upload Exam</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data" class="card p-3">
       <div class="mb-3">
        <label>Exam Name</label>
        <input type="text" name="exam_name" class="form-control" required 
               placeholder="e.g. Form 1 Mathematics exam,  Form 2 English exam">
    </div>
    <div class="mb-3">
        <label>Term</label>
        <input type="text" name="term" class="form-control" required 
               placeholder="e.g. Term 1, Term 2, Term 3">
    </div>
    <div class="mb-3">
        <label>Exam Type</label>
        <input type="text" name="exam_type" class="form-control" required 
               placeholder="e.g. CAT, Mid Term, End Term">
    </div>
    <div class="mb-3">
        <label>Year</label>
        <input type="number" name="year" class="form-control" required 
               placeholder="e.g. 2025">
    </div>
    <div class="mb-3">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control" required 
               placeholder="e.g. Mathematics, English, Biology">
    </div>
    <div class="mb-3">
        <label>Upload File</label>
        <input type="file" name="exam_file" class="form-control" required>
        <small class="form-text text-muted">Upload PDF, DOCX, or Excel file</small>
    </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

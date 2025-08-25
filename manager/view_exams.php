<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

// ✅ Only teachers and deans can view exams
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher','dean'])) {
    die("Access denied.");
}

$school_id = $_SESSION['school_id'];

// Fetch exams for this school with error handling
$sql = "SELECT * FROM exam WHERE school_id=? ORDER BY year DESC, term ASC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error); // Debugging info
}

$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Exams</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

    <h2 class="mb-4">Available Exams</h2>

    <table class="table table-bordered table-striped">
       <tr>
    <th>#</th>
    <th>Exam Name</th>
    <th>Term</th>
    <th>Exam Type</th>
    <th>Year</th>
    <th>Subject</th>
    <th>Download</th>
</tr>
<?php 
if ($result && $result->num_rows > 0): 
    $count = 1; // ✅ initialize counter
    while($row = $result->fetch_assoc()) { 
?>
    <tr>
        <td><?= $count++; ?></td> <!-- ✅ auto-increment counter -->
        <td><?= htmlspecialchars($row['exam_name']) ?></td>
        <td><?= htmlspecialchars($row['term']) ?></td>
        <td><?= htmlspecialchars($row['exam_type']) ?></td>
        <td><?= htmlspecialchars($row['year']) ?></td>
        <td><?= htmlspecialchars($row['subject']) ?></td>
        <td>
            <a href="download_exam.php?id=<?= $row['id'] ?>" 
               class="btn btn-success btn-sm">
               Download
            </a>
        </td>
    </tr>
            <?php } ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No exams found for your school.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

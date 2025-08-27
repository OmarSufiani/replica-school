<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$school_id = $_SESSION['school_id']; // ✅ restrict to logged-in school

// Only allow dean and teacher
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['dean', 'teacher'])) {
    $_SESSION['error'] = "You do not have permission to manage scores.";
    header("Location: add_score.php");
    exit;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM score WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $delete_id, $school_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Score deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete score: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle edit/update action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $newScore = intval($_POST['Score']);

    if ($newScore < 30) {
        $performance = "B.E";
        $autoComment = "Put more effort";
    } elseif ($newScore < 50) {
        $performance = "A.E";
        $autoComment = "Average";
    } elseif ($newScore < 70) {
        $performance = "M.E";
        $autoComment = "Good";
    } else {
        $performance = "E.E";
        $autoComment = "Excellent";
    }

    $stmt = $conn->prepare("UPDATE score SET Score=?, performance=?, tcomments=? 
                            WHERE id=? AND school_id=?");
    $stmt->bind_param("issii", $newScore, $performance, $autoComment, $id, $school_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Score updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update score: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Pagination setup
$limit = 18; // rows per page
$page = isset($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch filters
$selected_student_id = $_POST['student_id'] ?? '';
$selected_subject_id = $_POST['subject_id'] ?? '';
$selected_year       = $_POST['year'] ?? '';
$selected_term       = $_POST['term'] ?? '';
$selected_exam_type  = $_POST['exam_type'] ?? '';

// Base SQL for filtering
$sql = "
    SELECT s.id, s.std_id, s.subject_id, s.term, s.exam_type, s.class_id, 
           s.Score, s.performance, s.tcomments, s.school_id, s.created_at,
           st.firstname, st.lastname,
           sub.name AS subject_name,
           c.name AS class_name
    FROM score s
    JOIN student st ON s.std_id = st.id
    JOIN subject sub ON s.subject_id = sub.id
    JOIN class c ON s.class_id = c.id
    WHERE s.school_id = ?
";

$params = [$school_id];
$types = "i";

// Apply filters
if ($selected_student_id) {
    $sql .= " AND s.std_id = ?";
    $types .= "i";
    $params[] = $selected_student_id;
}
if ($selected_subject_id) {
    $sql .= " AND s.subject_id = ?";
    $types .= "i";
    $params[] = $selected_subject_id;
}
if ($selected_year) {
    $sql .= " AND YEAR(s.created_at) = ?";
    $types .= "i";
    $params[] = $selected_year;
}
if ($selected_term) {
    $sql .= " AND s.term = ?";
    $types .= "s";
    $params[] = $selected_term;
}
if ($selected_exam_type) {
    $sql .= " AND s.exam_type = ?";
    $types .= "s";
    $params[] = $selected_exam_type;
}

// Count total rows for pagination
$count_sql = "SELECT COUNT(*) as total FROM ($sql) as subquery";
$stmt_count = $conn->prepare($count_sql);
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt_count->close();

// Add limit/offset for paginated query
$sql .= " ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Fetch students & unique subjects for dropdowns (per school)
$students = $conn->query("SELECT id, firstname, lastname FROM student WHERE school_id='$school_id' ORDER BY firstname");

$subjects = $conn->query("
    SELECT MIN(id) as id, name 
    FROM subject 
    WHERE school_id='$school_id'
    GROUP BY name 
    ORDER BY name
");

// Terms and exam types
$terms = ["Term 1", "Term 2", "Term 3"];
$exam_types = ["CAT", "Mid Term", "End Term"];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Scores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>

    <h3 class="mb-4">Scores List</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" id="alert-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" id="alert-message"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="POST" class="card p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select">
                    <option value="">All Students</option>
                    <?php while ($st = $students->fetch_assoc()): ?>
                        <option value="<?= $st['id'] ?>" <?= $selected_student_id == $st['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($st['firstname']." ".$st['lastname']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select">
                    <option value="">All Subjects</option>
                    <?php while ($sub = $subjects->fetch_assoc()): ?>
                        <option value="<?= $sub['id'] ?>" <?= $selected_subject_id == $sub['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sub['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($selected_year) ?>" placeholder="YYYY">
            </div>

            <div class="col-md-2">
                <label class="form-label">Term</label>
                <select name="term" class="form-select">
                    <option value="">All Terms</option>
                    <?php foreach ($terms as $t): ?>
                        <option value="<?= $t ?>" <?= $selected_term == $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Exam Type</label>
                <select name="exam_type" class="form-select">
                    <option value="">All Exam Types</option>
                    <?php foreach ($exam_types as $e): ?>
                        <option value="<?= $e ?>" <?= $selected_exam_type == $e ? 'selected' : '' ?>><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <!-- Scores Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Term</th>
                    <th>Exam Type</th>
                    <th>Score</th>
                    <th>Performance</th>
                    <th>Comments</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($score = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $score['id'] ?></td>
                        <td><?= htmlspecialchars($score['firstname']." ".$score['lastname']) ?></td>
                        <td><?= htmlspecialchars($score['subject_name']) ?></td>
                        <td><?= htmlspecialchars($score['class_name']) ?></td>
                        <td><?= htmlspecialchars($score['term']) ?></td>
                        <td><?= htmlspecialchars($score['exam_type']) ?></td>
                        <td>
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="edit_id" value="<?= $score['id'] ?>">
                                <input type="number" name="Score" value="<?= $score['Score'] ?>" 
                                       class="form-control form-control-sm me-2" required>
                                <button type="submit" class="btn btn-sm btn-success">Update</button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($score['performance']) ?></td>
                        <td><?= htmlspecialchars($score['tcomments']) ?></td>
                        <td><?= $score['created_at'] ?></td>
                        <td>
                            <?php if ($_SESSION['role'] == 'dean'): ?>
                                <a href="?delete_id=<?= $score['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this score?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Back</a>
                </li>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<script>
    // Hide success/error messages after 3 seconds
    setTimeout(function(){
        let alertBox = document.getElementById("alert-message");
        if(alertBox){
            alertBox.style.transition = "opacity 0.5s";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }
    }, 3000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>


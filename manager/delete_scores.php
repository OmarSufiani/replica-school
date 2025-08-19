<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Only allow dean and teacher
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['dean', 'teacher'])) {
    $_SESSION['error'] = "You do not have permission to manage scores.";
    header("Location: add_score.php");
    exit;
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM score WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
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

    // Compute performance + comments automatically
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

    $stmt = $conn->prepare("UPDATE score SET Score=?, performance=?, tcomments=? WHERE id=?");
    $stmt->bind_param("issi", $newScore, $performance, $autoComment, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Score updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update score: " . $stmt->error;
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all scores with student, subject, and class names
$sql = "
    SELECT s.id, s.std_id, s.subject_id, s.term, s.exam_type, s.class_id, 
           s.Score, s.performance, s.tcomments, s.school_id, s.created_at,
           st.firstname, st.lastname,
           sub.name AS subject_name,
           c.name AS class_name
    FROM score s
    JOIN student_subject ss ON s.std_id = ss.student_id AND s.subject_id = ss.subject_id
    JOIN student st ON ss.student_id = st.id
    JOIN subject sub ON ss.subject_id = sub.id
    JOIN class c ON ss.class_id = c.id
    ORDER BY s.created_at DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("SQL Error: " . mysqli_error($conn)); // debug if something goes wrong
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Manage Scores</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">
        &larr; Back to Dashboard
    </a>

    <h3 class="mb-4">Scores List</h3>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" id="alert-message">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" id="alert-message">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

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
                    <th>School</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($score = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $score['id'] ?></td>
                        <td><?= htmlspecialchars($score['firstname'] . " " . $score['lastname']) ?></td>
                        <td><?= htmlspecialchars($score['subject_name']) ?></td>
                        <td><?= htmlspecialchars($score['class_name']) ?></td>
                        <td><?= htmlspecialchars($score['term']) ?></td>
                        <td><?= htmlspecialchars($score['exam_type']) ?></td>
                        <td>
                            <!-- Inline edit form for Score only -->
                            <form method="POST" class="d-flex">
                                <input type="hidden" name="edit_id" value="<?= $score['id'] ?>">
                                <input type="number" name="Score" value="<?= $score['Score'] ?>" 
                                       class="form-control form-control-sm me-2" required>
                                <button type="submit" class="btn btn-sm btn-success">Update</button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($score['performance']) ?></td>
                        <td><?= htmlspecialchars($score['tcomments']) ?></td>
                        <td><?= htmlspecialchars($score['school_id']) ?></td>
                        <td><?= $score['created_at'] ?></td>
                        <td>
                            <?php if ($_SESSION['role'] == 'dean'): ?>
                                <a href="?delete_id=<?= $score['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this score?');">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

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

</body>
</html>

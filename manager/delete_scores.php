<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit();
}
// Only allow admins and Superadmins
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    $_SESSION['error'] = "You do not have permission to delete scores.";
    header("Location: add_score.php"); // redirect to your scores page
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

// Fetch all scores
$result = mysqli_query($conn, "SELECT * FROM score ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Scores</title>
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
                    <th>Student ID</th>
                    <th>Subject ID</th>
                    <th>Term</th>
                    <th>Exam Type</th>
                    <th>Class ID</th>
                    <th>Score</th>
                    <th>Performance</th>
                    <th>Comments</th>
                    <th>School ID</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($score = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $score['id'] ?></td>
                        <td><?= htmlspecialchars($score['std_id']) ?></td>
                        <td><?= htmlspecialchars($score['subject_id']) ?></td>
                        <td><?= htmlspecialchars($score['term']) ?></td>
                        <td><?= htmlspecialchars($score['exam_type']) ?></td>
                        <td><?= htmlspecialchars($score['class_id']) ?></td>
                        <td><?= htmlspecialchars($score['Score']) ?></td>
                        <td><?= htmlspecialchars($score['performance']) ?></td>
                        <td><?= htmlspecialchars($score['tcomments']) ?></td>
                        <td><?= htmlspecialchars($score['school_id']) ?></td>
                        <td><?= $score['created_at'] ?></td>
                        <td>
                            <?php if (in_array($_SESSION['role'], ['admin', 'Superadmin'])): ?>
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

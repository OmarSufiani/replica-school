<?php
session_start();
include 'db.php';

// ✅ Restrict to admin and teacher only
if (
    !isset($_SESSION['user_id']) || 
    !isset($_SESSION['school_id']) || 
    ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teacher')
) {
    die("❌ Unauthorized access");
}

$school_id = $_SESSION['school_id'];

// ✅ Delete teacher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM teacher WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    echo "<div class='alert alert-success'>✅ Teacher deleted successfully</div>";
}

// ✅ Update teacher
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $enrolment_no = $_POST['enrolment_no'];
    $date_hired = $_POST['date_hired'];

    $stmt = $conn->prepare("UPDATE teacher SET name=?, enrolment_no=?, date_hired=? WHERE id=? AND school_id=?");
    $stmt->bind_param("sssii", $name, $enrolment_no, $date_hired, $id, $school_id);
    $stmt->execute();
    echo "<div class='alert alert-success'>✅ Teacher updated successfully</div>";
}

// ✅ Fetch teachers for this school
$stmt = $conn->prepare("SELECT * FROM teacher WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">
    &larr; Back to Dashboard
</a>
    <h2 class="mb-4">👨‍🏫 Manage Teachers</h2>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Enrolment No</th>
                <th>Date Hired</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="post">
                    <td><?= $row['id']; ?></td>
                    <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']); ?>" class="form-control" required></td>
                    <td><input type="text" name="enrolment_no" value="<?= htmlspecialchars($row['enrolment_no']); ?>" class="form-control" required></td>
                    <td><input type="date" name="date_hired" value="<?= $row['date_hired']; ?>" class="form-control" required></td>
                    <td>
                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                        <button type="submit" name="update" class="btn btn-success btn-sm">Save</button>
                        <a href="?delete=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
